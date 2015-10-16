**[Promise API documentation](../api/promises.md)**

Icicle implements promises based on the [Promises/A+](http://promisesaplus.com) specification, adding support for cancellation.

Promises are objects that act as placeholders for the future value of an asynchronous operation. Pending promises may either be fulfilled with any value (including other promises, `null`, and exceptions) or rejected with any value (non-exceptions are encapsulated in an exception). Once a promise is fulfilled or rejected (resolved) with a value, the promise cannot becoming pending and the resolution value cannot change.

Callback functions are the primary way of accessing the resolution value of promises. Unlike other APIs that use callbacks, **promises provide an execution context to callback functions, allowing callbacks to return values and throw exceptions**.

All promise objects implement a common interface: `Icicle\Promise\PromiseInterface`. While the primary promise implementation is `Icicle\Promise\Promise`, several other classes also implement `Icicle\Promise\PromiseInterface`.

The `Icicle\Promise\PromiseInterface::then(callable $onFulfilled = null, callable $onRejected = null)` method is the primary way to register callbacks that receive either the value used to fulfill the promise or the exception used to reject the promise. A promise is returned by `then()`, which is resolved with the return value of a callback or rejected if a callback throws an exception.

The `Icicle\Promise\PromiseInterface::done(callable $onFulfilled = null, callable $onRejected = null)` method registers callbacks that should either consume promised values or handle errors. No value is returned from `done()`. Values returned by callbacks registered using `done()` are ignored and exceptions thrown from callbacks are re-thrown in an uncatchable way.

*[More on using callbacks to interact with promises...](../api/promises.md#interacting-with-promises)*

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Dns\Executor\Executor;
use Icicle\Dns\Resolver\Resolver;
use Icicle\Loop;
use Icicle\Socket\Client\ClientInterface;
use Icicle\Socket\Client\Connector;

$resolver = new Resolver(new Executor('8.8.8.8'));

// Method returning a Generator used to create a Coroutine (a type of promise)
$promise1 = new Coroutine($resolver->resolve('example.com'));

$promise2 = $promise1->then(
    function (array $ips) { // Called if $promise1 is fulfilled.
        $connector = new Connector();
        return new Coroutine($connector->connect($ips[0], 80)); // Return another promise.
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

The example above uses the [DNS component][dns] to resolve the IP address for a domain, then connect to the resolved IP address. The `resolve()` method of `$resolver` and the `connect()` method of `$connector` both return promises. `$promise1` created by `resolve()` will either be fulfilled or rejected:

- If `$promise1` is fulfilled, the callback function registered in the call to `$promise1->then()` is executed, using the fulfillment value of `$promise1` as the argument to the function. The callback function then returns the promise from `connect()`. The resolution of `$promise2` will then be determined by the resolution of this returned promise (`$promise2` will adopt the state of the promise returned by `connect()`).
- If `$promise1` is rejected, `$promise2` is rejected since no `$onRejected` callback was registered in the call to `$promise1->then()`

*[More on promise resolution and propagation...](../api/promises.md#resolution-and-propagation)*

## Brief overview of promise API features

- Asynchronous resolution (callbacks are not called before `then()` or `done()` return).
- Convenience methods for registering special callbacks to handle promise resolution.
- Lazy execution of promise-creating functions.
- Operations on collections of promises to join, select, iterate, and map to other promises or values.
- Support for promise cancellation.
- Methods to convert synchronous functions or callback-based functions into functions accepting and returning promises.


[dns]:          ../api/dns.md
