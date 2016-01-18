The standard observable implementation.

**Implements**
:   [`Icicle\Observable\Observable`](Observable.md)


## __construct()

    new Emitter(
        callable(
            callable(mixed $value): \Generator $emit
        ): \Generator $emitter,
        callable $onDisposed = null
    )

Creates a new emitter from a generator function. The function is passed a coroutine function that can be used to emit values.

### Parameters
`$emitter`
:   A coroutine function that emits values.

`$onDisposed`
:   A callback function to invoke when the emitter is disposed of.
