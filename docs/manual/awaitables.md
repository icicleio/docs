Icicle implements awaitables based on the [Promises/A+](http://promisesaplus.com) specification, adding support for cancellation.

Awaitables are objects that act as placeholders for the future value of an asynchronous operation. Pending awaitables may either be fulfilled with any value (including other awaitables, `null`, and exceptions) or rejected with any value (non-exceptions are encapsulated in an exception). Once an awaitable is fulfilled or rejected (resolved) with a value, the awaitable cannot becoming pending and the resolution value cannot change.

Callback functions are the primary way of accessing the resolution value of awaitables. Unlike other APIs that use callbacks, **awaitables provide an execution context to callback functions, allowing callbacks to return values and throw exceptions**.

All awaitable objects implement a common interface: `\Icicle\Awaitable\Awaitable`. The three primary awaitable implementations are `\Icicle\Awaitable\Delayed`, `\Icicle\Awaitable\Promise`, and `\Icicle\Coroutine\Coroutine`, all extending a base class `\Icicle\Awaitable\Future`. `\Icicle\Awaitable\Delayed` can be publicly resolved, while `\Icicle\Awaitable\Promise` can only be resolved by a function passed to the constructor. `\Icicle\Coroutine\Coroutine` is a special class created using generators and are discussed in the [next section](coroutines.md). There are also several other classes in Icicle also implement `\Icicle\Awaitable\Awaitable`, but instances of these classes are primarily returned by promise methods or functions and should not be manually constructed. 

The `\Icicle\Awaitable\Awaitable::then(callable $onFulfilled = null, callable $onRejected = null)` method is the primary way to register callbacks that receive either the value used to fulfill the awaitable or the exception used to reject the awaitable. A awaitable is returned by `then()`, which is resolved with the return value of a callback or rejected if a callback throws an exception.

The `\Icicle\Awaitable\Awaitable::done(callable $onFulfilled = null, callable $onRejected = null)` method registers callbacks that should either consume awaitabled values or handle errors. No value is returned from `done()`. Values returned by callbacks registered using `done()` are ignored and exceptions thrown from callbacks are re-thrown in an uncatchable way.

*[More on using callbacks to interact with awaitables...](#awaitable-chaining)*

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Dns\Executor\BasicExecutor;
use Icicle\Dns\Resolver\BasicResolver;
use Icicle\Loop;
use Icicle\Socket\Socket;
use Icicle\Socket\Client\DefaultConnector;

$resolver = new BasicResolver(new BasicExecutor('8.8.8.8'));

// Method returning a Generator used to create a Coroutine (a type of awaitable)
$awaitable1 = new Coroutine($resolver->resolve('example.com'));

$awaitable2 = $awaitable1->then(
    function (array $ips) { // Called if $awaitable1 is fulfilled.
        $connector = new DefaultConnector();
        return new Coroutine($connector->connect($ips[0], 80)); // Return another awaitable.
        // $awaitable2 will adopt the state of the awaitable returned above.
    }
);

$awaitable2->done(
    function (Socket $client) { // Called if $awaitable2 is fulfilled.
        echo "Asynchronously connected to example.com:80\n";
    },
    function (\Exception $exception) { // Called if $awaitable1 or $awaitable2 is rejected.
        echo "Asynchronous task failed: {$exception->getMessage()}\n";
    }
);

Loop\run();
```

The example above uses the [DNS component](../api/dns.md) to resolve the IP address for a domain, then connect to the resolved IP address. The `resolve()` method of `$resolver` and the `connect()` method of `$connector` both return awaitables. `$awaitable1` created by `resolve()` will either be fulfilled or rejected:

- If `$awaitable1` is fulfilled, the callback function registered in the call to `$awaitable1->then()` is executed, using the fulfillment value of `$awaitable1` as the argument to the function. The callback function then returns the awaitable from `connect()`. The resolution of `$awaitable2` will then be determined by the resolution of this returned awaitable (`$awaitable2` will adopt the state of the awaitable returned by `connect()`).
- If `$awaitable1` is rejected, `$awaitable2` is rejected since no `$onRejected` callback was registered in the call to `$awaitable1->then()`

*[More on awaitable resolution and propagation...](#resolution-and-propagation)*


## Brief overview of awaitable API features
- Asynchronous resolution (callbacks are not called before `then()` or `done()` return).
- Convenience methods for registering special callbacks to handle awaitable resolution.
- Lazy execution of awaitable-creating functions.
- Operations on collections of awaitables to join, select, iterate, and map to other awaitables or values.
- Support for awaitable cancellation.
- Methods to convert synchronous functions or callback-based functions into functions accepting and returning awaitables.


## Resolution and Propagation

### Child Awaitable Resolution

When an awaitable is resolved with a value (or fulfilled), each callback registered to receive the awaitable fulfillment value is invoked. Similarly, when an awaitable is rejected with an exception, each callback registered to receive the awaitable rejection reason is invoked.

When a callback is registered using a method that returns another awaitable (i.e., `then()`, `always()`, and `capture()`), the return value of the callback is used to fulfill that awaitable, or if an exception is thrown, reject that awaitable.

```php
$awaitable2 = $awaitable1->then(
    function ($value) {
        if (null === $value) {
            throw new Exception('Value cannot be null.'); // Throwing rejects $awaitable2 with the exception.
        }
        return $value + 1; // Returning a value will fulfill $awaitable2 with that value.
    },
    function (Exception $exception) {
        return 1; // Returning from the rejected handler fulfills $awaitable2 with that value.
    }
);
```

If a callback is omitted when calling `then()`, the returned awaitable is then fulfilled or rejected using the same value or exception as the parent awaitable. The example below is similar to the example above, except the `$onRejected` parameter of `then()` is now `null`. If `$awaitable1` is rejected, `$awaitable2` is also rejected with the same exception.

```php
$awaitable2 = $awaitable1->then(
    function ($value) {
        if (null === $value) {
            throw new Exception('Value cannot be null.'); // Throwing rejects $awaitable2 with the exception.
        }
        return $value + 1; // Returning a value will fulfill $awaitable2 with that value.
    }
    // No $onRejected callback given, so if $awaitable1 rejects, $awaitable2 will automatically be rejected
    // with the same exception as $awaitable1.
);
```

Similarly, if no `$onFulfilled` callback is given, `$awaitable2` is fulfilled with the same value as `$awaitable1` if `$awaitable1` is fulfilled.

```php
$awaitable2 = $awaitable1->then(
    null, // No $onFulfilled callback given, so if $awaitable1 is fulfilled, $awaitable2 is fulfilled with
          // the same value as $awaitable1.
    function (Exception $exception) {
        return 1; // Returning from the rejected handler fulfills $awaitable2 with that value.
    }
);
```

### Asynchronous Callback Invocation

Invocation of callbacks registered to an awaitable is guaranteed to be asynchronous. This means that registered callbacks will not be invoked until after `then()`, `done()` have returned and execution has left the current scope (i.e., the calling function returns). To make this clearer, consider the example below.

```php
$awaitable->then(function ($value) {
    echo "{1}";
});
echo "{2}";
```

If callbacks were invoked immediately on registration if an awaitable was resolved, the output of the above code would depend on the state of `$awaitable`. If the awaitable was fulfilled, `{1}{2}` would be echoed. If the awaitable was pending, `{2}{1}` would be output.

While this example is contrived, this behavior can have significant consequences when working with objects or referenced variables. To ensure consistent behavior, callbacks registered to awaitables are *always* invoked asynchronously.

### Awaitable Chaining

```php
use Icicle\Loop;
use Icicle\Awaitable\Delayed;

$delayed = new Delayed();

$delayed
    ->then(function ($value) {
        $value = (int) $value;
        if (0 === $value) {
            throw new RuntimeException('Value cannot be 0.');
        }
        return 100 / $value;
    })
    ->then(function ($value) {
        return $value * $value;
    })
    ->capture(function (RuntimeException $e) {
        return 0; // Analogous to a try/catch block.
    })
    ->done(
        function ($value) {
            echo "Result: {$value}\n";
        },
        function (Exception $e) {
            echo "Error: {$e->getMessage()}\n";
        }
    );

$delayed->resolve(0); // Echos "Result: 0"

Loop\run();
```
In the example above, resolving the awaitable with `0` causes the first callback to throw an exception. This exception is used to reject the returned awaitable. No rejection callback was registered on the awaitable returned from the first call to `then()`, so that awaitable is automatically rejected with the same exception. The awaitable returned from the second call to `then()` had a rejection callback registered using `capture()` with a type-hint of `RuntimeException`, which matches the type of the thrown exception, so the callback is invoked. That callback returns `0`, fulfilling the awaitable returned from `capture()` with that value. The awaitable returned from `capture()` had a fulfillment and rejection callback registered with `done()`. Since the awaitable was resolved with `0`, the fulfillment callback is invoked, echoing `Result: 0`.

##### Another Example

```php
use Icicle\Dns\Executor\BasicExecutor;
use Icicle\Dns\Resolver\BasicResolver;
use Icicle\Loop;
use Icicle\Socket\Socket;
use Icicle\Socket\Connector\DefaultConnector;

$resolver = new BasicResolver(new BasicExecutor('8.8.8.8'));

$awaitable1 = $resolver->resolve('example.com'); // Method returning an awaitable.

$awaitable2 = $awaitable1->then(
    function (array $ips) { // Called if $awaitable1 is fulfilled.
        $connector = new DefaultConnector();
		return $connector->connect($ips[0], 80); // Method returning an awaitable.
		// $awaitable2 will adopt the state of the awaitable returned above.
    }
);

$awaitable2->done(
    function (Socket $client) { // Called if $awaitable2 is fulfilled.
        echo "Asynchronously connected to example.com:80\n";
    },
    function (\Exception $exception) { // Called if $awaitable1 or $awaitable2 is rejected.
        echo "Asynchronous task failed: {$exception->getMessage()}\n";
    }
);

Loop\run();
```

In the example above, the `resolve()` method of `$resolver` and the `connect()` method of `$connector` both return awaitables. `$awaitable1` created by `resolve()` will either be fulfilled or rejected:

- If `$awaitable1` is fulfilled, the callback function registered in the call to `$awaitable1->then()` is executed, using the fulfillment value of `$awaitable1` as the argument to the function. The callback function then returns the awaitable from `connect()`. The resolution of `$awaitable2` will then be determined by the resolution of this returned awaitable (`$awaitable2` will adopt the state of the awaitable returned by `connect()`).
- If `$awaitable1` is rejected, `$awaitable2` is rejected since no `$onRejected` callback was registered in the call to `$awaitable1->then()`

### Error Handling

When an awaitable is rejected, the exception used to reject the awaitable is not thrown, it is only given to callbacks registered using the methods described above. However, if `done()` is called on an awaitable without an `$onRejected` callback and that awaitable is rejected, the exception will be re-thrown in an uncatchable way (see the [Loop](../Loop) component for more on uncatchable exceptions).

Error handling with awaitables comes down to a simple rule: Call `done()` on the awaitable to consume the final result or handle any exceptions, or return the awaitable to the caller, thereby delegating error handling to the code requesting the awaitable resolution value.

### Iterative Resolution

Awaitable resolution is handled iteratively, so there is no concern of overflowing the call stack regardless of how deep the chain may have become. The example below demonstrates how a chain of 100 awaitables maintains a constant call stack size when the registered callbacks are invoked.

```php
use Icicle\Loop;
use Icicle\Awaitable\Delayed;

$awaitable = new Delayed();

for ($i = 0; $i < 100; ++$i) {
    $awaitable = $awaitable->then(function ($value) {
        printf("%3d) %d\n", $value, xdebug_get_stack_depth()); // Stack size is constant
        return ++$value;
    });
}

$awaitable->resolve(1);

Loop\run();
```

When an awaitable is resolved with another awaitable the original awaitable transfers the responsibility of invoking registered callbacks to the awaitable used for resolution. A awaitable may be fulfilled any number of times with another awaitable, and the call stack will not overflow when the awaitable is eventually resolved.


### Cancellation

If an awaitable is still pending, the awaitable may be cancelled using the `cancel()` method ([see prototype for more information](#awaitableinterface-cancel)). This immediately rejects the awaitable, and calls any cancellation callback that may have been provided when the awaitable was created.

When cancelling a child awaitable (an awaitable returned by `then()` or other methods returning another awaitable), the parent awaitable is also cancelled if there are no other pending children. The parent process is only cancelled if all children are also cancelled.

```php
$parent = new Awaitable(function ($resolve, $reject) { /* ... */ });

$child1 = $parent->then();
$child2 = $parent->then();

$child1->cancel(); // Cancels only $child1.

$child2->cancel(); // Cancels both $child2 and $parent.
```


## Acknowledgements
The behavior and interface of Icicle's awaitable interface was inspired by the [when.js](https://github.com/cujojs/when) promise implementation for JavaScript.
