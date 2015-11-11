Icicle implements promises based on the [Promises/A+](http://promisesaplus.com) specification, adding support for cancellation.

Promises are objects that act as placeholders for the future value of an asynchronous operation. Pending promises may either be fulfilled with any value (including other promises, `null`, and exceptions) or rejected with any value. (Rejection values that are not exceptions are wrapped by an exception of type `Icicle\Promise\Exception\ReasonException` that has a `getReason()` method returning the original value.) Once a promise is fulfilled or rejected (resolved) with a value, the promise cannot becoming pending and the resolution value cannot change.

Callback functions are the primary way of accessing the resolution value of promises. Unlike other APIs that use callbacks, **promises provide an execution context to callback functions, allowing callbacks to return values and throw exceptions**.

Callback functions registered to promises are always [invoked asynchronously](#asynchronous-callback-invocation) to ensure consistent behavior regardless of the state of the promise at the time callbacks are registered.



## Creating a Promise

Promises can be created in a few different ways depending on your needs. All promises implement `Icicle\Promise\PromiseInterface`, which is described in the section on [interacting with promises](#interacting-with-promises).

!!! tip
    It is rare to need to create a promise instance yourself in Icicle. Usually a promise is created by calling a method or function that returns a promise or by creating a [Coroutine](coroutine.md), which are also promises.


### Promise

When a `Icicle\Promise\Promise` object is created, it invokes a resolver function given to the constructor with the following prototype: `callable<void (callable (void (mixed $value = null) $resolve, callable (void (Exception $exception) $reject>`. The resolver function initiates the (asynchronous) computation, calling the `$resolve($value = null)` function with the resolution value or `$reject(mixed $reason)` with an exception. An optional cancellation function with the prototype `callable<void Exception $exception>` can also be provided that is called if the promise is cancelled.

```php
use Icicle\Promise\Promise;

$resolver = function ($resolve, $reject) {
    // Initiate asynchronous computation.
    // $resolve and $reject can be directly called
    // or passed as callbacks to other functions.
    $resolve($result);
};

$onCancelled = function (Exception $exception) {
    // Perform any necessary cleanup.
};

$promise = new Promise($resolver, $onCancelled);
```

This may at first glance seem like an usual way to perform an operation and return a value. Remember that the code contained in the resolver function is not meant to be strictly synchronous code, but rather it is meant to perform an asynchronous operation and will likely define other callback functions, calling `$resolve` or `$reject` sometime after the resolver function has been executed. Remember that promises may also be resolved with other promises, causing the resolved promise to adopt the state of that promise (that is, passing a promise to `$resolve` will fulfill or reject the promise when the passed promise is fulfilled or rejected).

If the resolver function throws an exception, the promise is rejected with that exception.


##### Example

The following code creates a promise that is resolved when a connection is successfully made to a server. The `connect()` method of the `Icicle\Socket\Client\Connector` class in the [Socket](socket.md) component use a similar approach to establish connections asynchronously.

```php
use Icicle\Loop;
use Icicle\Promise\Promise;

$promise = new Promise(
    function ($resolve, $reject) use (&$await) {
        $client = stream_socket_client(
            'tcp://8.8.8.8:53',
            $errno,
            $errstr,
            null,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT
        );

        if (!$client || $errno) {
            // Rejects promise.
            throw new Exception('Could not connect to DNS server.');
        }

        $await = Loop\await($client, function ($client, $expired) use (
                &$await, $resolve, $reject
            ) {
            if ($expired) {
                $reject('Connecting to the DNS server timed out.');
            } else {
                $resolve($client);
            }
            $await->free();
        });

        $await->listen(10);
    },
    function (Exception $exception) use (&$await) {
        $await->free();
    }
);

// Use result of promise by calling $promise->then(), $promise->done(), etc.

Loop\run();
```


### Deferred

When a task is instigated in one piece of code and completed in another (e.g., separate methods of an object), a `Icicle\Promise\Deferred` object can be used to encapsulate a promise and control the state of that promise externally. A `Icicle\Promise\Deferred` object is designed to be kept private by the code that wishes to control the state of the promise (e.g., a class), while being able to provide the promise to consuming code through the `getPromise()` method. A cancellation function may optionally be provided to the constructor when creating a `Icicle\Promise\Deferred` object that is called if the encapsulated promise is cancelled.

```php
use Icicle\Promise\Deferred;

$onCancelled = function (Exception $exception) {
    // Perform any necessary cleanup.
};

$deferred = new Deferred($onCancelled);

$promise = $deferred->getPromise();
```

`Icicle\Promise\Deferred` objects have only three methods other than the constructor:
- `Deferred::resolve(mixed $value = null): void`: Resolves the encapsulated promise with the given value or promise.
- `Deferred::reject($reason = null): void`: Rejects the encapsulated promise with the given exception.
- `Deferred::getPromise(): PromiseInterface`: Returns the encapsulated promise so it can be given to consumers.

Other objects can be created that can act like a deferred by implementing `Icicle\Promise\PromisorInterface`.


### Lazy Promise

A lazy promise is created by calling the function `Icicle\Promise\lazy()`, passing another function as an argument that creates a promise that is only called once the result of the promise is requested. That is, the function creating the promise is not called until a callback using the resolution value of the promise is registered using `then()`, `done()`, etc. Lazy promises provide an easy way to perform operations only as needed for a computation. The promise returned from this function may be treated like any other promise.

```php
use Icicle\Promise;

$promisor = function () {
    $promise = doSomethingAsynchronously(); // Function returning a promise.
    return $promise->then(function ($result) {
        // Use result to perform another computation.
    });
};

$lazy = Promise\lazy($promisor); // $promiser will not be called here.

// Other code...

// $promisor called only when a callback is registered to the promise.
$lazy->done(
    function ($result) {
        // Use $result.
    },
    function (Exception $e) {
        // Handle exception.
    }
);
```


### resolve()

    Promise\resolve(mixed $value = null): PromiseInterface

The `Icicle\Promise\resolve()` function returns a fulfilled promise using the given value. There are two possible outcomes depending on the type of the passed value:
    1. `Icicle\Promise\PromiseInterface`: The promise is returned without modification.
    2. All other types: A fulfilled promise is returned using the given value as the result.

### reject()

    Promise\reject(mixed $reason = null): PromiseInterface

The `Icicle\Promise\reject()` function returns a rejected promise using the given reason. If `$reason` is not an exception, an instance of `Icicle\Promise\Exception\RejectedException` is created using the reason.


## PromiseInterface

All promise objects implement `Icicle\Promise\PromiseInterface`, which provides a variety of functions for registering callbacks to receive the resolution value of a promise. While the primary promise implementation is `Icicle\Promise\Promise`, several other classes in this component also implement `Icicle\Promise\PromiseInterface`.


### then()

    PromiseInterface::then(
        callable<mixed (mixed $value)> $onFulfilled = null,
        callable<mixed (Exception $exception)> $onRejected = null
    ): PromiseInterface

This method is the primary way to register callbacks that receive either the value used to fulfill the promise or the exception used to reject the promise. Another `Icicle\Promise\PromiseInterface` object is returned by this method, which is resolved with the return value of a callback or rejected if a callback throws an exception. For more on how promises are resolved by callbacks, see the section on [Resolution and Propagation](#resolution-and-propagation).

##### Parameters
`$onFulfilled`
:   Callback function that is invoked with the resolved value if the promise is fulfilled.

`$onRejected`
:   Callback function that is invoked with an exception if the promise is rejected.


### done()

    PromiseInterface::done(
        callable<void (mixed $value)> $onFulfilled = null,
        callable<void (Exception $exception)> $onRejected = null
    ): void

This method registers callbacks that should either consume promised values or handle errors. No value is returned from `done()`. Values returned by callbacks registered using this method are ignored and exceptions thrown from callbacks are re-thrown in an *uncatchable* way.

##### Parameters
`$onFulfilled`
:   Callback function that is invoked with the resolved value if the promise is fulfilled.

`$onRejected`
:   Callback function that is invoked with an exception if the promise is rejected.


### cancel()

    PromiseInterface::cancel(mixed $reason = null): void

Cancels the promise with the given reason. Canceling a promise rejects the promise with the given exception and calls the cancellation callback if one was provided when the promise was created. The parent promise is also cancelled if no other children of that parent have been created.

##### Parameters
`$reason`
:   The exception to cancel the promise with, if any. If `$reason` is not an exception, an instance of `Icicle\Promise\Exception\CancelledException` is created using the reason.


### timeout()

    PromiseInterface::timeout(
        float $timeout,
        mixed $reason = null
    ): PromiseInterface

Returns a promise that is rejected in `$timeout` seconds with the given exception if the promise is not resolved before that time. When the promise resolves, the returned promise is fulfilled or rejected with the same value.

##### Parameters
`$timeout`
:   The timeout for the promise in seconds.

`$reason`
:   The exception to cancel the promise with, if any. If `$reason` is not an exception, an instance of `Icicle\Promise\Exception\CancelledException` is created using the reason.


### delay()

    PromiseInterface::delay(float $time): PromiseInterface

Returns a promise that is fulfilled `$time` seconds after this promise is fulfilled. If the promise is rejected, the returned promise is immediately rejected.

##### Parameters
`$time`
:   The delay amount in seconds.


### capture()

    PromiseInterface::capture(
        callable<(Exception $exception): mixed> $onRejected
    ): PromiseInterface

Assigns a callback function that is called when the promise is rejected. If a type-hint is defined on the callable (e.g.: `function (RuntimeException $exception) { /* ... */ }`, then the function will only be called if the exception is an instance of the type-hinted exception.

##### Parameters
`$onRejected`
:   The callback function to call.

##### Example

```php
$promise2 = $promise1->capture(function (RuntimeException $exception) {
    // This function is only called if $promise1 is rejected with an instance of RuntimeException.
    // Otherwise $promise2 is rejected with the same exception as $promise1.
});
```


### tap()

    PromiseInterface PromiseInterface::tap(
        callable<(mixed $value): PromiseInterface|null> $onFulfilled
    ): PromiseInterface

Calls the given function with the value used to fulfill the promise, then fulfills the returned promise with the same value. If the promise is rejected, the returned promise is also rejected and `$onFulfilled` is not called. If `$onFulfilled` throws an exception, the returned promise is rejected with the thrown exception. The return value of `$onFulfilled` is not used.

##### Parameters
`$onFulfilled`
:   The callback function to call.


### cleanup()

    PromiseInterface PromiseInterface::cleanup(
        callable<(mixed $value): PromiseInterface|null> $onResolved
    ): PromiseInterface

The callback given to this function will be called if the promise is fulfilled or rejected. The callback is called with no arguments. If the callback does not throw, the returned promise is resolved in the same way as the original promise. That is, it is fulfilled or rejected with the same value or exception. If `$onResolved` throws an exception, the returned promise is rejected with the thrown exception. The return value of `$onResolved` is not used.

##### Parameters
`$onResolved`
:   The callback function to call.


### splat()

    PromiseInterface PromiseInterface::splat(
        callable<(mixed ...$args): mixed> $onFulfilled
    ): PromiseInterface

If a promise fulfills with an array or `Traversable`, this method uses the elements of the array (or each value of the `Traversable`) as arguments to the given callback function similar to the `...` (splat) operator. If the promise does not fulfill with an array or `Traversable`, the returned promise is rejected with an instance of `\Icicle\Promise\Exception\TypeException`. Otherwise the returned promise is resolved as though the callback function was registered with `then()`. If the promise is rejected, the returned promise is also rejected.

##### Parameters
`$onFulfilled`
:   The callback function to call.


### isPending()

    PromiseInterface::isPending(): bool

Determines if the promise has been resolved.


### isFulfilled()

    PromiseInterface::isFulfilled(): bool

Determines if the promise has been fulfilled.


### isRejected()

    PromiseInterface::isRejected(): bool

Determines if the promise has been rejected.


### wait()

    PromiseInterface::wait(): mixed

This function may be used to synchronously wait for a promise to be resolved. This function should generally not be used within a running event loop, but rather to set up a task (or set of tasks, then use join() or another function to group them) and synchronously wait for the task to complete. Using this function in a running event loop will not block the loop, but it will prevent control from moving past the call to this function and disrupt program flow.

The fulfillment value of the promise is returned or the exception used to reject the promise is thrown from the function.



## Functions

The `Icicle\Promise` namespace contains several functions for performing operations on sets of promises. All functions in this section are designed so most of their parameters may either be promises or values (or an array containing any combination of promises and values). `Icicle\Promise\resolve()` is used on all values to create promises.


### Promise\settle()

    PromiseInterface Promise\settle(mixed[] $promises)

Returns a promise that is resolved when all promises are resolved. The returned promise will not reject by itself (only if cancelled). Returned promise is fulfilled with an array of resolved promises, with keys identical and corresponding to the original given array. The `$promises` array may contain any combination of promises or values.

##### Parameters
`$promises`
:   An array of values or promises to settle.


### Promise\all()

    Promise\all(mixed[] $promises): PromiseInterface

Returns a promise that is fulfilled when all promises are fulfilled, and rejected if any promise is rejected. Returned promise is fulfilled with an array of values used to fulfill each contained promise, with keys corresponding to the array of promises or values. The `$promises` array may contain any combination of promises or values.

##### Parameters
`$promises`
:   An array of promises or values.


### Promise\any()

    Promise\any(mixed[] $promises): PromiseInterface

Returns a promise that is fulfilled when any promise is fulfilled, and rejected only if all promises are rejected. The `$promises` array may contain any combination of promises or values.

##### Parameters
`$promises`
:   An array of promises or values.


### Promise\some()

Promise\some(mixed[] $promises, int $required): PromiseInterface

Returns a promise that is fulfilled when $required number of promises are fulfilled. The promise is rejected if `$required` number of promises can no longer be fulfilled. The `$promises` array may contain any combination of promises or values.

##### Parameters
`$promises`
:   An array of promises or values.

`$required`
:   The number of promises required to be fulfilled.


### Promise\choose()

    Promise\choose(mixed[] $promises): PromiseInterface

Returns a promise that is fulfilled or rejected when the first promise is fulfilled or rejected. The `$promises` array may contain any combination of promises or values.

##### Parameters
`$promises`
:   An array of promises or values.


### Promise\map()

    Promise\map(
        callable<(mixed ...$values): mixed> $callback,
        mixed[] ...$promises
    ): PromiseInterface[]

Maps the callback to each promise as it is fulfilled. Returns an array of promises resolved by the return callback value of the callback function. The callback may return promises or throw exceptions to reject promises in the array. If a promise in the passed array rejects, the callback will not be called and the promise in the array is rejected for the same reason. The `$promises` array may contain any combination of promises or values.

!!! tip
    Use the `all()` or `settle()` functions to determine when all promises in the array have been resolved.

##### Parameters
`$callback`
:   A callback function to apply to each resolved value.

`...$promises`
:   Arrays of promises or values.


### Promise\reduce()

    Promise\reduce(
        mixed[] $promises,
        callable<(mixed $carry): mixed> $callback,
        mixed $initial = null
    ): PromiseInterface

Reduce function similar to `array_reduce()`, only it works on promises and/or values. The `$promises` array may contain any combination of promises or values. The callback function may return a promise or value and `$initial` value may also be a promise or value.

##### Parameters
`$promises`
:   Array of promises or values.

`$callback`
:   A callback function to apply to each resolved value in `$promises`. The previous reduced value will be passed to the callback until the entire array has been reduced.

`$initial`
:   The inital value for `$carry` to pass to the reduce function on the first element.


### Promise\iterate()

    Promise\iterate(
        callable<(mixed $carry): mixed> $worker,
        callable<(mixed $carry): mixed> $predicate,
        mixed $seed = null
    ): PromiseInterface

Calls `$worker` using the return value of the previous call until `$predicate` returns a truthy value. `$predicate` is called before `$worker` with the value to be passed to `$worker`. If `$worker` or `$predicate` throws an exception, the promise is rejected using that exception. The call stack is cleared before each call to `$worker` to avoid filling the call stack. If `$worker` returns a promise, iteration waits for the returned promise to be resolved.

##### Parameters
`$worker`
:   The callback function to use as the worker.

`$predicate`
:   The callback function to use as the worker.

`$seed`
:   The value to use as the initial parameter to `$worker`. If `$seed` is a promise, it will be resolved first and the resolved value used as the seed.


### Promise\retry()

    Promise\retry(
        callable<(): mixed> $promisor,
        callable<(Exception $exception): bool> $onRejected
    ): PromiseInterface

Continuously calls `$promisor`, a function that should return a promise (though it can actually return any type of value). If the promise returned by `$promisor` is rejected, `$onRejected` is called with the rejection exception. If `$onRejected` returns a falsey value, `$promisor` is called again to retry the operation, otherwise the promise returned by `retry()` is rejected with the same exception. Once the promise returned by `$promisor` is fulfilled, the promise returned by `retry()` is fulfilled with the same value. If either `$promisor` or `$onRejected` throw an exception, the promise returned by `retry()` is rejected with that exception.

##### Parameters
`$promisor`
:   The callback function to call initially.

`$onRejected`
:   The callback function to call whenever `$promisor` is rejected.


### Promise\lift()

    Promise\lift(
        callable<(mixed ...$args): mixed> $worker
    ): callable<(mixed ...$args): PromiseInterface>

Wraps the given callable `$worker` in a promise aware function that takes the same number of arguments as `$worker`, but those arguments may be promises for the future argument value or just values. The returned function will return a promise for the return value of `$worker` and will never throw. The `$worker` function will not be called until each promise given as an argument is fulfilled. If any promise provided as an argument rejects, the promise returned by the returned function will be rejected for the same reason. The promise is fulfilled with the return value of `$worker` or rejected if `$worker` throws.

##### Parameters
`$worker`
:   The function to wrap in a promise-aware wrapper function.


### Promise\promisify()

    Promise\promisify(
        callable<(mixed ...$args): mixed> $worker,
        int $index = 0
    ): callable<(mixed ...$args): PromiseInterface>

Transforms a function `$worker` that takes a callback into a function that returns a promise. The promise is fulfilled with an array of the parameters that would have been passed to the callback function. The function returned from this method takes the same arguments as `$worker` except for the callback function, which is replaced by this function.

##### Parameters
`$worker`
:   The function to wrap that takes a callback.

`$index`
:   The index of the callback parameter to promisify.


### Promise\adapt()

    Promise\adapt(object $thenable): PromiseInterface

Adapts any object with a `then(callable $onFulfilled, callable $onRejected)` method to an promise implementing `Icicle\Promise\PromiseInterface`. This allows Icicle to use promises or futures generated by other libraries.

##### Parameters
`$thenable`
:   The foreign thenable object to wrap in an Iclce promise.
