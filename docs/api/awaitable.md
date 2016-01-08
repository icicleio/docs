Icicle implements awaitables based on the [Promises/A+](http://promisesaplus.com) specification, adding support for cancellation.

Awaitables are objects that act as placeholders for the future value of an asynchronous operation. Pending awaitables may either be fulfilled with any value (including other awaitables, `null`, and exceptions) or rejected with an exception. Once an awaitable is fulfilled or rejected (resolved) with a value, the awaitable cannot becoming pending and the resolution value cannot change.

Callback functions are the primary way of accessing the resolution value of awaitables. Unlike other APIs that use callbacks, **awaitables provide an execution context to callback functions, allowing callbacks to return values and throw exceptions**.

Callback functions registered to awaitables are always [invoked asynchronously](../manual/awaitables.md#asynchronous-callback-invocation) to ensure consistent behavior regardless of the state of the awaitable at the time callbacks are registered.


## Awaitable

All awaitable objects implement `Icicle\Awaitable\Awaitable`, which provides a variety of functions for registering callbacks to receive the resolution value of an awaitable. While the primary awaitable implementation is `Icicle\Awaitable\Awaitable`, several other classes in this component also implement `Icicle\Awaitable\Awaitable`.


### then()

    Awaitable::then(
        callable(mixed $value): mixed $onFulfilled = null,
        callable(\Exception $exception): mixed $onRejected = null
    ): Awaitable

This method is the primary way to register callbacks that receive either the value used to fulfill the awaitable or the exception used to reject the awaitable. Another `Icicle\Awaitable\Awaitable` object is returned by this method, which is resolved with the return value of a callback or rejected if a callback throws an exception. For more on how awaitables are resolved by callbacks, see the section on [Resolution and Propagation](#resolution-and-propagation).

#### Parameters
`callable(mixed $value): mixed $onFulfilled`
:   Callback function that is invoked with the resolved value if the awaitable is fulfilled.

`callable(\Exception $exception): mixed $onRejected`
:   Callback function that is invoked with an exception if the awaitable is rejected.


### done()

    Awaitable::done(
        callable(mixed $value): mixed $onFulfilled = null,
        callable(\Exception $exception): mixed $onRejected = null
    ): void

This method registers callbacks that should either consume awaitabled values or handle errors. No value is returned from `done()`. Values returned by callbacks registered using this method are ignored and exceptions thrown from callbacks are re-thrown in an *uncatchable* way.

#### Parameters
`callable(mixed $value): mixed $onFulfilled`
:   Callback function that is invoked with the resolved value if the awaitable is fulfilled.

`callable(\Exception $exception): mixed $onRejected`
:   Callback function that is invoked with an exception if the awaitable is rejected.


### cancel()

    Awaitable::cancel(\Exception $reason = null): void

Cancels the awaitable with the given reason. Canceling an awaitable rejects the awaitable with the given exception and calls the cancellation callback if one was provided when the awaitable was created. The parent awaitable is also cancelled if no other children of that parent have been created.

#### Parameters
`\Exception $reason`
:   The exception to cancel the awaitable with, if any. If `$reason` is not an exception, an instance of `Icicle\Awaitable\Exception\CancelledException` is created using the reason.


### timeout()

    Awaitable::timeout(
        float $timeout,
        callable(): mixed $onTimeout = null
    ): Awaitable

Returns an awaitable that is rejected in `$timeout` seconds with the given exception if the awaitable is not resolved before that time. When the awaitable resolves, the returned awaitable is fulfilled or rejected with the same value.

#### Parameters
`float $timeout`
:   The timeout for the awaitable in seconds.

`callable(): mixed $onTimeout`
:   The function to invoke if the timeout expires. This function will resolve the returned awaitable. If no callback function is given, the returned awaitable will be rejected with an instance of `Icicle\Awaitable\Exception\TimeoutException`.


### delay()

    Awaitable::delay(float $time): Awaitable

Returns an awaitable that is fulfilled `$time` seconds after this awaitable is fulfilled. If the awaitable is rejected, the returned awaitable is immediately rejected.

#### Parameters
`float $time`
:   The delay amount in seconds.


### capture()

    Awaitable::capture(
        callable(\Exception $exception): mixed $onRejected
    ): Awaitable

Assigns a callback function that is called when the awaitable is rejected. If a type-hint is defined on the callable (e.g.: `function (\RuntimeException $exception) { /* ... */ }`, then the function will only be called if the exception is an instance of the type-hinted exception.

#### Parameters
`callable(\Exception $exception): mixed $onRejected`
:   The callback function to call.

##### Example

```php
$awaitable2 = $awaitable1->capture(function (\RuntimeException $exception) {
    // This function is only called if $awaitable1 is rejected with an instance of RuntimeException.
    // Otherwise $awaitable2 is rejected with the same exception as $awaitable1.
});
```


### tap()

    Awaitable Awaitable::tap(
        callable(mixed $value): Awaitable|null $onFulfilled
    ): Awaitable

Calls the given function with the value used to fulfill the awaitable, then fulfills the returned awaitable with the same value. If the awaitable is rejected, the returned awaitable is also rejected and `$onFulfilled` is not called. If `$onFulfilled` throws an exception, the returned awaitable is rejected with the thrown exception. The return value of `$onFulfilled` is not used.

#### Parameters
`callable(mixed $value): Awaitable|null $onFulfilled`
:   The callback function to call.


### cleanup()

    Awaitable Awaitable::cleanup(
        callable(): Awaitable|null $onResolved
    ): Awaitable

The callback given to this function will be called if the awaitable is fulfilled or rejected. The callback is called with no arguments. If the callback does not throw, the returned awaitable is resolved in the same way as the original awaitable. That is, it is fulfilled or rejected with the same value or exception. If `$onResolved` throws an exception, the returned awaitable is rejected with the thrown exception. The return value of `$onResolved` is not used.

#### Parameters
`callable(): Awaitable|null $onResolved`
:   The callback function to call.


### splat()

    Awaitable Awaitable::splat(
        callable(mixed ...$args): mixed $onFulfilled
    ): Awaitable

If an awaitable fulfills with an array or `Traversable`, this method uses the elements of the array (or each value of the `Traversable`) as arguments to the given callback function similar to the `...` (splat) operator. If the awaitable does not fulfill with an array or `Traversable`, the returned awaitable is rejected with an instance of `Icicle\Awaitable\Exception\TypeException`. Otherwise the returned awaitable is resolved as though the callback function was registered with `then()`. If the awaitable is rejected, the returned awaitable is also rejected.

#### Parameters
`callable(mixed ...$args): mixed$onFulfilled`
:   The callback function to call.


### isPending()

    Awaitable::isPending(): bool

Determines if the awaitable has been resolved.


### isFulfilled()

    Awaitable::isFulfilled(): bool

Determines if the awaitable has been fulfilled.


### isRejected()

    Awaitable::isRejected(): bool

Determines if the awaitable has been rejected.


### wait()

    Awaitable::wait(): mixed

This function may be used to synchronously wait for an awaitable to be resolved. This function should generally not be used within a running event loop, but rather to set up a task (or set of tasks, then use join() or another function to group them) and synchronously wait for the task to complete. Using this function in a running event loop will not block the loop, but it will prevent control from moving past the call to this function and disrupt program flow.

The fulfillment value of the awaitable is returned or the exception used to reject the awaitable is thrown from the function.


## Creating an Awaitable

Awaitables can be created in a few different ways depending on your needs. All awaitables implement `Icicle\Awaitable\Awaitable`, which is described in the section below.

!!! tip
    It is rare to need to create an awaitable instance yourself in Icicle. Usually an awaitable is created by calling a method or function that returns an awaitable or by creating a [Coroutine](coroutine.md) (a special type of awaitable) from a method or function returning a `\Generator`.


### Promise

When an `Icicle\Awaitable\Promise` object is created, it invokes a resolver function given to the constructor with the following prototype: `callable(callable(mixed $value = null): void $resolve, callable(\Exception $exception): void $reject): callable|null`. The resolver function initiates the (asynchronous) computation, calling the `$resolve($value = null)` function with the resolution value or `$reject(\Exception $reason)` with an exception. An optional cancellation function with the prototype `callable(\Exception $exception): void` can be returned from the resolver function. The cancellation function is invoked if the awaitable is cancelled.

```php
use Icicle\Awaitable\Promise;

$resolver = function (callable $resolve, callable $reject) {
    // Initiate asynchronous computation.
    // $resolve and $reject can be directly called
    // or passed as callbacks to other functions.
    $resolve($result);
    
    return function (\Exception $exception) {
        // Perform any necessary cleanup when the awaitable is cancelled.
    };
};

$awaitable = new Promise($resolver);
```

This may at first glance seem like an usual way to perform an operation and return a value. Remember that the code contained in the resolver function is not meant to be strictly synchronous code, but rather it is meant to perform an asynchronous operation and will likely define other callback functions, calling `$resolve` or `$reject` sometime after the resolver function has been executed. Remember that awaitables may also be resolved with other awaitables, causing the resolved awaitable to adopt the state of that awaitable (that is, passing an awaitable to `$resolve` will fulfill or reject the awaitable when the passed awaitable is fulfilled or rejected).

If the resolver function throws an exception, the awaitable is rejected with that exception.


##### Example

The following code creates an awaitable that is resolved when a connection is successfully made to a server. The `connect()` method of the `Icicle\Socket\Client\Connector` class in the [Socket](socket.md) component use a similar approach to establish connections asynchronously.

```php
use Icicle\Loop;
use Icicle\Loop\Watcher\Io;
use Icicle\Awaitable\Promise;

$awaitable = new Promise(
    function (callable $resolve, callable $reject) {
        $client = stream_socket_client(
            'tcp://8.8.8.8:53',
            $errno,
            $errstr,
            null,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT
        );

        if (!$client || $errno) {
            // Rejects awaitable.
            throw new Exception('Could not connect to DNS server.');
        }

        $await = Loop\await($client, function ($client, $expired, Io $await) use (
            $resolve, $reject
        ) {
            $await->free();
            
            if ($expired) {
                $reject('Connecting to the DNS server timed out.');
                return;
            }
            
            $resolve($client);
        });

        $await->listen(10);
        
        return function (\Exception $exception) use ($await) {
            $await->free();
        };
    }
);

// Use result of awaitable by calling $awaitable->then(), $awaitable->done(), etc.

Loop\run();
```


### Delayed

A `Icicle\Awaitable\Delayed` object is a publicly resolvable awaitable. This class has public methods `resolve()` and `reject()`, allowing the awaitable object to be resolved even by consumers of the awaitable (code using the awaitable rather than code that created the awaitable). *These objects should not be returned as part of a public interface*, but rather used internally within objects or within [coroutines](../manual/coroutines.md). The constructor takes an optional function that is invoked if the awaitable is cancelled.

`Icicle\Awaitable\Delayed` should be used when possible as it is more performant than `Icicle\Awaitable\Promise` (and easier to create and resolve). This is the most common type of awaitable used in Icicle since most functions and methods are written as [coroutines](../manual/coroutines.md), never exposing the awaitables used within the coroutine as part of a public API.

```php
use Icicle\Awaitable\Delayed;

$delayed = new Delayed(function (\Exception $exception) {
   // Perform an necessary cleanup on cancellation.
});

$delayed->resolve(1); // Resolves the awaitable with the integer 1.
```

### Deferred

When a task is instigated in one piece of code and completed in another (e.g., separate methods of an object), an `Icicle\Awaitable\Deferred` object can be used to encapsulate an awaitable and control the state of that awaitable externally. An `Icicle\Awaitable\Deferred` object is designed to be kept private by the code that wishes to control the state of the awaitable (e.g., a class), while being able to provide the awaitable to consuming code through the `getPromise()` method. A cancellation function may optionally be provided to the constructor when creating a `Deferred` object that is called if the encapsulated awaitable is cancelled.

```php
use Icicle\Awaitable\Deferred;

$onCancelled = function (\Exception $exception) {
   // Perform an necessary cleanup on cancellation.
};

$deferred = new Deferred($onCancelled);

$awaitable = $deferred->getPromise();
```

`Icicle\Awaitable\Deferred` objects have only three methods other than the constructor:
- `Deferred::resolve(mixed $value = null): void`: Resolves the encapsulated promise with the given value or awaitable.
- `Deferred::reject(\Exception $reason): void`: Rejects the encapsulated awaitable with the given exception.
- `Deferred::getPromise(): Promise`: Returns the encapsulated promise so it can be given to consumers.


### Lazy Awaitable

A lazy awaitable is created by calling the function `Icicle\Awaitable\lazy()`, passing another function as an argument that creates an awaitable that is only called once the result of the awaitable is requested. That is, the function creating the awaitable is not called until a callback using the resolution value of the awaitable is registered using `then()`, `done()`, etc. Lazy awaitables provide an easy way to perform operations only as needed for a computation. The awaitable returned from this function may be treated like any other awaitable.

```php
use Icicle\Awaitable;

$promisor = function () {
    $awaitable = doSomethingAsynchronously(); // Function returning an awaitable.
    return $awaitable->then(function ($result) {
        // Use result to perform another computation.
    });
};

$lazy = Awaitable\lazy($promisor); // $awaitabler will not be called here.

// Other code...

// $promisor called only when a callback is registered to the awaitable.
$lazy->done(
    function ($result) {
        // Use $result.
    },
    function (\Exception $e) {
        // Handle exception.
    }
);
```


## Functions

The `Icicle\Awaitable` namespace contains several functions for performing operations on sets of awaitables. All functions in this section are designed so most of their parameters may either be awaitables or values (or an array containing any combination of awaitables and values). `Icicle\Awaitable\resolve()` is used on all values to create awaitables.

### resolve()

    Icicle\Awaitable\resolve(mixed $value = null): Icicle\Awaitable\Awaitable

The `Icicle\Awaitable\resolve()` function returns a fulfilled awaitable using the given value. There are two possible outcomes depending on the type of the passed value:
    1. `Icicle\Awaitable\Awaitable`: The awaitable is returned without modification.
    2. All other types: A fulfilled awaitable is returned using the given value as the result.

#### Return value
`Icicle\Awaitable\Awaitable`
:   The passed awaitable or a fulfilled awaitable using the given value as the fulfillment value.

### reject()

    Icicle\Awaitable\reject(\Exception $reason): Icicle\Awaitable\Awaitable

The `Icicle\Awaitable\reject()` function returns a rejected awaitable using the given exception as the rejection reason.

#### Return value
`Icicle\Awaitable\Awaitable`
:   Rejected awaitable using the given exception as the rejection reason.

### settle()

    Icicle\Awaitable\settle(mixed[] $awaitables): Icicle\Awaitable\Awaitable

Returns an awaitable that is resolved when all awaitables are resolved. The returned awaitable will not reject by itself (only if cancelled). Returned awaitable is fulfilled with an array of resolved awaitables, with keys identical and corresponding to the original given array. The `$awaitables` array may contain any combination of awaitables or values.

#### Parameters
`mixed[] $awaitables`
:   An array of values or awaitables to settle.

### all()

    Icicle\Awaitable\all(mixed[] $awaitables): Icicle\Awaitable\Awaitable

Returns an awaitable that is fulfilled when all awaitables are fulfilled, and rejected if any awaitable is rejected. Returned awaitable is fulfilled with an array of values used to fulfill each contained awaitable, with keys corresponding to the array of awaitables or values. The `$awaitables` array may contain any combination of awaitables or values.

#### Parameters
`mixed[] $awaitables`
:   An array of awaitables or values.

#### Return value
`Icicle\Awaitable\Awaitable`
:   Awaitable fulfilled only if all awaitables are fulfilled and rejected if any awaitables are rejected.

### any()

    Icicle\Awaitable\any(mixed[] $awaitables): Icicle\Awaitable\Awaitable

Returns an awaitable that is fulfilled when any awaitable is fulfilled, and rejected only if all awaitables are rejected. The `$awaitables` array may contain any combination of awaitables or values.

#### Parameters
`mixed[] $awaitables`
:   An array of awaitables or values.

#### Return value
`Icicle\Awaitable\Awaitable`
:   Awaitable fulfilled when any of the given awaitables is fulfilled and rejected only if all the given awaitables are rejected.

### some()

    Awaitable\some(mixed[] $awaitables, int $required): Icicle\Awaitable\Awaitable

Returns an awaitable that is fulfilled when $required number of awaitables are fulfilled. The awaitable is rejected if `$required` number of awaitables can no longer be fulfilled. The `$awaitables` array may contain any combination of awaitables or values.

#### Parameters
`mixed[] $awaitables`
:   An array of awaitables or values.

`int $required`
:   The number of awaitables required to be fulfilled.

#### Return value
`Icicle\Awaitable\Awaitable`
:   Awaitable fulfilled once enough awaitables are fulfilled or rejected if too many awaitables are rejected.

### choose()

    Icicle\Awaitable\choose(mixed[] $awaitables): Icicle\Awaitable\Awaitable

Returns an awaitable that is fulfilled or rejected when the first awaitable is fulfilled or rejected. The `$awaitables` array may contain any combination of awaitables or values.

#### Parameters
`mixed[] $awaitables`
:   An array of awaitables or values.

#### Return value
`Icicle\Awaitable\Awaitable`
:   Awaitable fulfilled with the fulfillment value of the first fulfilled awaitable or rejected if all awaitables are rejected.

### map()

    Icicle\Awaitable\map(
        callable(mixed ...$values): mixed $callback,
        mixed[] ...$awaitables
    ): Icicle\Awaitable\Awaitable[]

Maps the callback to each awaitable as it is fulfilled. Returns an array of awaitables resolved by the return callback value of the callback function. The callback may return awaitables or throw exceptions to reject awaitables in the array. If an awaitable in the passed array rejects, the callback will not be called and the awaitable in the array is rejected for the same reason. The `$awaitables` array may contain any combination of awaitables or values.

!!! tip
    Use the `all()` or `settle()` functions to determine when all awaitables in the array have been resolved.

#### Parameters
`callable(mixed ...$values): mixed $callback`
:   A callback function to apply to each resolved value.

`mixed[] ...$awaitables`
:   Arrays of awaitables or values.

#### Return value
`Icicle\Awaitable\Awaitable[]`
:   Array of awaitable resolved by the return value of the mapped callback function.

### reduce()

    Icicle\Awaitable\reduce(
        mixed[] $awaitables,
        callable(mixed $carry): mixed $callback,
        mixed $initial = null
    ): Icicle\Awaitable\Awaitable

Reduce function similar to `array_reduce()`, only it works on awaitables and/or values. The `$awaitables` array may contain any combination of awaitables or values. The callback function may return an awaitable or value and `$initial` value may also be an awaitable or value.

#### Parameters
`mixed[] $awaitables`
:   Array of awaitables or values.

`callable(mixed $carry): mixed $callback`
:   A callback function to apply to each resolved value in `$awaitables`. The previous reduced value will be passed to the callback until the entire array has been reduced.

`mixed $initial`
:   The inital value for `$carry` to pass to the reduce function on the first element.

#### Return value
`Icicle\Awaitable\Awaitable`
:   Awaitable fulfilled with the final carry value when all awaitables have been fulfilled.

### lift()

    Icicle\Awaitable\lift(
        callable(mixed ...$args): mixed $worker
    ): callable(mixed ...$args): Icicle\Awaitable\Awaitable

Wraps the given callable `$worker` in an awaitable aware function that takes the same number of arguments as `$worker`, but those arguments may be awaitables for the future argument value or just values. The returned function will return an awaitable for the return value of `$worker` and will never throw. The `$worker` function will not be called until each awaitable given as an argument is fulfilled. If any awaitable provided as an argument rejects, the awaitable returned by the returned function will be rejected for the same reason. The awaitable is fulfilled with the return value of `$worker` or rejected if `$worker` throws.

#### Parameters
`callable(mixed ...$args): mixed $worker`
:   The function to wrap in an awaitable-aware wrapper function.

#### Return value
`callable(mixed ...$args): Icicle\Awaitable\Awaitable`
:   Callable function accepting awaitables or values for arguments, returning another awaitable.

### promisify()

    Icicle\Awaitable\promisify(
        callable(mixed ...$args): mixed $worker,
        int $index = 0
    ): callable(mixed ...$args): Icicle\Awaitable\Awaitable

Transforms a function `$worker` that takes a callback into a function that returns an awaitable. The awaitable is fulfilled with an array of the parameters that would have been passed to the callback function. The function returned from this method takes the same arguments as `$worker` except for the callback function, which is replaced by this function.

#### Parameters
`callable(mixed ...$args): mixed $worker`
:   The function to wrap that takes a callback.

`int $index`
:   The index of the callback parameter to promisify.

#### Return value
`Icicle\Awaitable\Awaitable`
:   Awaitable fulfilled with an array of the parameters passed to the callback function.

### adapt()

    Icicle\Awaitable\adapt(object $thenable): Icicle\Awaitable\Awaitable

Adapts any object with a `then(callable $onFulfilled, callable $onRejected)` method to an awaitable implementing `Icicle\Awaitable\Awaitable`. This allows Icicle to use awaitables or futures generated by other libraries.

#### Parameters
`object $thenable`
:   The foreign thenable object to wrap in an Icicle awaitable.

#### Return value
`Icicle\Awaitable\Awaitable`
:   Awaitable resolved by the adapted thenable.