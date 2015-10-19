## Resolution and Propagation

### Child Promise Resolution

When a promise is resolved with a value (or fulfilled), each callback registered to receive the promise fulfillment value is invoked. Similarly, when a promise is rejected with an exception, each callback registered to receive the promise rejection reason is invoked.

When a callback is registered using a method that returns another promise (i.e., `then()`, `always()`, and `capture()`), the return value of the callback is used to fulfill that promise, or if an exception is thrown, reject that promise.

```php
$promise2 = $promise1->then(
    function ($value) {
        if (null === $value) {
            throw new Exception('Value cannot be null.'); // Throwing rejects $promise2 with the exception.
        }
        return $value + 1; // Returning a value will fulfill $promise2 with that value.
    },
    function (Exception $exception) {
        return 1; // Returning from the rejected handler fulfills $promise2 with that value.
    }
);
```

If a callback is omitted when calling `then()`, the returned promise is then fulfilled or rejected using the same value or exception as the parent promise. The example below is similar to the example above, except the `$onRejected` parameter of `then()` is now `null`. If `$promise1` is rejected, `$promise2` is also rejected with the same exception.

```php
$promise2 = $promise1->then(
    function ($value) {
        if (null === $value) {
            throw new Exception('Value cannot be null.'); // Throwing rejects $promise2 with the exception.
        }
        return $value + 1; // Returning a value will fulfill $promise2 with that value.
    }
    // No $onRejected callback given, so if $promise1 rejects, $promise2 will automatically be rejected
    // with the same exception as $promise1.
);
```

Similarly, if no `$onFulfilled` callback is given, `$promise2` is fulfilled with the same value as `$promise1` if `$promise1` is fulfilled.

```php
$promise2 = $promise1->then(
    null, // No $onFulfilled callback given, so if $promise1 is fulfilled, $promise2 is fulfilled with
          // the same value as $promise1.
    function (Exception $exception) {
        return 1; // Returning from the rejected handler fulfills $promise2 with that value.
    }
);
```

### Asynchronous Callback Invocation

Invocation of callbacks registered to a promise is guaranteed to be asynchronous. This means that registered callbacks will not be invoked until after `then()`, `done()` have returned and execution has left the current scope (i.e., the calling function returns). To make this clearer, consider the example below.

```php
$promise->then(function ($value) {
    echo "{1}";
});
echo "{2}";
```

If callbacks were invoked immediately on registration if a promise was resolved, the output of the above code would depend on the state of `$promise`. If the promise was fulfilled, `{1}{2}` would be echoed. If the promise was pending, `{2}{1}` would be output.

While this example is contrived, this behavior can have significant consequences when working with objects or referenced variables. To ensure consistent behavior, callbacks registered to promises are *always* invoked asynchronously.

### Promise Chaining

```php
use Icicle\Loop;
use Icicle\Promise\Deferred;

$deferred = new Deferred();

$deferred->getPromise()
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

$deferred->resolve(0); // Echos "Result: 0"

Loop\run();
```
In the example above, resolving the promise with `0` causes the first callback to throw an exception. This exception is used to reject the returned promise. No rejection callback was registered on the promise returned from the first call to `then()`, so that promise is automatically rejected with the same exception. The promise returned from the second call to `then()` had a rejection callback registered using `capture()` with a type-hint of `RuntimeException`, which matches the type of the thrown exception, so the callback is invoked. That callback returns `0`, fulfilling the promise returned from `capture()` with that value. The promise returned from `capture()` had a fulfillment and rejection callback registered with `done()`. Since the promise was resolved with `0`, the fulfillment callback is invoked, echoing `Result: 0`.

##### Another Example

```php
use Icicle\Dns\Executor\Executor;
use Icicle\Dns\Resolver\Resolver;
use Icicle\Loop;
use Icicle\Socket\Client\ClientInterface;
use Icicle\Socket\Client\Connector;

$resolver = new Resolver(new Executor('8.8.8.8'));

$promise1 = $resolver->resolve('example.com'); // Method returning a promise.

$promise2 = $promise1->then(
    function (array $ips) { // Called if $promise1 is fulfilled.
        $connector = new Connector();
		return $connector->connect($ips[0], 80); // Method returning a promise.
		// $promise2 will adopt the state of the promise returned above.
    }
);

$promise2->done(
    function (ClientInterface $client) { // Called if $promise2 is fulfilled.
        echo "Asynchronously connected to example.com:80\n";
    },
    function (Exception $exception) { // Called if $promise1 or $promise2 is rejected.
        echo "Asynchronous task failed: {$exception->getMessage()}\n";
    }
);

Loop\run();
```

In the example above, the `resolve()` method of `$resolver` and the `connect()` method of `$connector` both return promises. `$promise1` created by `resolve()` will either be fulfilled or rejected:

- If `$promise1` is fulfilled, the callback function registered in the call to `$promise1->then()` is executed, using the fulfillment value of `$promise1` as the argument to the function. The callback function then returns the promise from `connect()`. The resolution of `$promise2` will then be determined by the resolution of this returned promise (`$promise2` will adopt the state of the promise returned by `connect()`).
- If `$promise1` is rejected, `$promise2` is rejected since no `$onRejected` callback was registered in the call to `$promise1->then()`

### Error Handling

When a promise is rejected, the exception used to reject the promise is not thrown, it is only given to callbacks registered using the methods described above. However, if `done()` is called on a promise without an `$onRejected` callback and that promise is rejected, the exception will be re-thrown in an uncatchable way (see the [Loop](../Loop) component for more on uncatchable exceptions).

Error handling with promises comes down to a simple rule: Call `done()` on the promise to consume the final result or handle any exceptions, or return the promise to the caller, thereby delegating error handling to the code requesting the promise resolution value.

### Iterative Resolution

Promise resolution is handled iteratively, so there is no concern of overflowing the call stack regardless of how deep the chain may have become. The example below demonstrates how a chain of 100 promises maintains a constant call stack size when the registered callbacks are invoked.

```php
use Icicle\Loop;
use Icicle\Promise\Deferred;

$deferred = new Deferred();
$promise = $deferred->getPromise();

for ($i = 0; $i < 100; ++$i) {
    $promise = $promise->then(function ($value) {
        printf("%3d) %d\n", $value, xdebug_get_stack_depth()); // Stack size is constant
        return ++$value;
    });
}

$deferred->resolve(1);

Loop\run();
```

When a promise is resolved with another promise the original promise transfers the responsibility of invoking registered callbacks to the promise used for resolution. A promise may be fulfilled any number of times with another promise, and the call stack will not overflow when the promise is eventually resolved.


## Cancellation

If a promise is still pending, the promise may be cancelled using the `cancel()` method ([see prototype for more information](#promiseinterface-cancel)). This immediately rejects the promise, and calls any cancellation callback that may have been provided when the promise was created.

When cancelling a child promise (a promise returned by `then()` or other methods returning another promise), the parent promise is also cancelled if there are no other pending children. The parent process is only cancelled if all children are also cancelled.

```php
$parent = new Promise(function ($resolve, $reject) { /* ... */ });

$child1 = $parent->then();
$child2 = $parent->then();

$child1->cancel(); // Cancels only $child1.

$child2->cancel(); // Cancels both $child2 and $parent.
```
