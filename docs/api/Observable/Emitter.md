The standard observable implementation.

**Implements**
:   [`Icicle\Observable\Observable`](Observable.md)


## __construct()

    new Emitter(
        callable(): \Generator $emitter,
        callable $onDisposed = null
    )

Creates a new emitter from a generator function. Each value yielded by the function is emitted by the emitter.

### Parameters
`$emitter`
:   A generator function or coroutine that yields values to be emitted.

`$onDisposed`
:   A callback function to invoke when the emitter is disposed of.
