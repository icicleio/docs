Coroutines are interruptible functions implemented using [Generators](http://www.php.net/manual/en/language.generators.overview.php) and [Awaitables](../Awaitable/index.md). See the [manual documentation for coroutines](../../manual/coroutines.md) for details on how to create and use coroutines.


## Coroutines as Awaitables

`Icicle\Coroutine\Coroutine` implements `Icicle\Awaitable\Awaitable`. **Any methods available on awaitables are also available on coroutines and a coroutine can be treated just like any other awaitable.**

See the [Awaitable API documentation](../Awaitable/index.md) for the complete list of the methods available in `Icicle\Awaitable\Awaitable` and the other methods available for working with awaitables.


## Functions

### wrap()

    Coroutine\wrap(
        callable(mixed ...$args): \Generator $callback
    ): callable(mixed ...$args): Coroutine

Returns a `callable` that returns a `Icicle\Coroutine\Coroutine` by calling `$callback` that must return a `Generator` written to be a coroutine. Any arguments given to the returned callable are also passed to `$callback`.

#### Parameters
`callable(mixed ...$args): \Generator $callback`
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
        callable(mixed ...$args): \Generator $callback,
        mixed ...$args
    ): Coroutine

Creates and runs a new coroutine from a given generator function.

#### Parameters
`callable(mixed ...$args): \Generator$callback`
:   The generator function to call and start a coroutine from. The return type of the callback should be `Generator`.

`mixed ...$args`
:   Arguments to pass to the generator function.

#### Return value
A new coroutine for the given callback function. The created coroutine will begin running immediately.


### run()

    Coroutine\run(
        callable(mixed ...$args): \Generator $worker,
        mixed ...$args
    ): mixed

Calls the function (which should return a `\Generator` written as a coroutine), then runs the coroutine.

!!! warning
    This function should not be called within a running event loop. This function is meant to be used to create an initial coroutine that runs the rest of the application. The resolution value of the coroutine is returned or the rejection reason is thrown from this function.

##### Parameters
`callable(mixed ...$args): \Generator $worker`
:   Function returning a `Generator` written as a coroutine.

`mixed ...$args`
:   Arguments to pass to `$worker`.


### sleep()

    Coroutine\sleep(float $time): \Generator

Sleeps the current coroutine asynchronously for a given number of seconds.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`float $time`
:   The amount of time to sleep in seconds.

#### Resolves
`float`
:   The amount of time actually slept.

!!! tip
    Whenever you're tempted to use the [`sleep()`](http://php.net/sleep) built-in function, you should probably be using this function instead!
