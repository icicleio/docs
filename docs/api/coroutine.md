Coroutines are interruptible functions implemented using [Generators](http://www.php.net/manual/en/language.generators.overview.php). See the [manual documentation for coroutines](../manual/coroutines.md) for details on how to create and use coroutines.

## Coroutines as Promises

`Icicle\Coroutine\Coroutine` implements `Icicle\Coroutine\CoroutineInterface`, which extends `Icicle\Promise\PromiseInterface`. **Any methods available on promises are also available on coroutines and a coroutine can be treated just like any other promise.**

See the [Promise API documentation](promise.md) for the complete list of the methods available in `Icicle\Promise\PromiseInterface` and the other methods available for working with promises.



## CoroutineInterface

### Coroutine Constructor

```php
$coroutine = new Coroutine(Generator $generator)
```

As shown in the examples above, a `Icicle\Coroutine\Coroutine` instance can be created by passing a `Generator` to the constructor. Execution of the coroutine is begun asynchronously, after leaving the calling scope of the constructor (e.g. after the function calling the constructor returns).


### CoroutineInterface::pause()

    CoroutineInterface::pause(): void

Pauses the coroutine once it reaches a `yield` statement (if executing). If the coroutine was already at a `yield` statement (or has not begun execution), no further code will be executed until resumed with `resume()`. Any promises that the coroutine is currently waiting for will continue to do work to be resolved, but once resolved, the coroutine will not continue until resumed.


### CoroutineInterface::resume()

    CoroutineInterface::resume(): void

Resumes the coroutine if it was paused. If the coroutine was waiting for a promise to resolve, the coroutine will not continue execution until the promise has resolved.


### CoroutineInterface::isPaused()

    CoroutineInterface::isPaused(): bool

Determines if the coroutine is currently paused. Note that true is only returned if the coroutine was explicitly paused. It does not return true if the coroutine is waiting for a promise to resolve.

#### Return value
A boolean indicating if the coroutine is currently paused.


### CoroutineInterface::cancel()

    CoroutineInterface::cancel(mixed $reason = null): void

Cancels execution of the coroutine. If the coroutine is waiting on a promise, that promise is cancelled with the given exception. If no exception is given, an instance of `Icicle\Promise\Exception\CancelledException` will be used.

#### Parameters
`$reason`
:   An exception or value to cancel the coroutine with.



## Functions

### wrap()

    Coroutine\wrap(
        callable<(mixed ...$args): Generator> $callback
    ): callable<(mixed ...$args): CoroutineInterface>

Returns a `callable` that returns a `Icicle\Coroutine\Coroutine` by calling `$callback` that must return a `Generator` written to be a coroutine. Any arguments given to the returned callable are also passed to `$callback`.

#### Parameters
`$callback`
:   A generator function to create a coroutine function from.

```php
use Icicle\Coroutine;
use Icicle\Loop;

$callback = Coroutine\wrap(function ($value) {
    $value = (yield $value + 1);
    echo '{' . $value . '}';
    $value = (yield $value + 1);
    echo '{' . $value . '}';
    $value = (yield $value + 1);
    echo '{' . $value . '}';
});

$callback(10);
$callback(20);
$callback(30);

Loop\run();
```

The example above will output `{11}{21}{31}{12}{22}{32}{13}{23}{33}`, demonstrating how a generator function can be used to create multiple coroutines. This example also demonstrates the cooperative execution of coroutines.


### create()

    Coroutine\create(
        callable<(mixed ...$args): Generator> $callback,
        mixed ...$args
    ): CoroutineInterface

Creates and runs a new coroutine from a given generator function.

#### Parameters
`$callback`
:   The generator function to call and start a coroutine from. The return type of the callback should be `Generator`.

`...$args`
:   Arguments to pass to the generator function.

#### Return value
A new coroutine for the given callback function. The created coroutine will begin running immediately.


### run()

    Coroutine\run(
        callable<(mixed ...$args): Generator> $worker,
        mixed ...$args
    ): mixed

Calls the function (which should return a `Generator` written as a coroutine), then runs the coroutine. This function should not be called within a running event loop. This function is meant to be used to create an initial coroutine that runs the rest of the application. The resolution value of the coroutine is returned or the rejection reason is thrown from this function.

##### Parameters
`$worker`
:   Function returning a `Generator` written as a coroutine.

`...$args`
:   Arguments to pass to `$worker`.


### sleep()

    Coroutine\sleep(float $time): Generator

Sleeps the current coroutine asynchronously for a given number of seconds.

#### Parameters
`$time`
:   The amount of time to sleep in seconds.

#### Return value
A generator that resolves with the actual amount of time slept.

!!! tip
    Whenever you're tempted to use the [`sleep()`](http://php.net/sleep) built-in function, you should probably be using this function instead!
