Encapsulates an [`Emitter`](Emitter.md) to allow emitting values from multiple places.


## __construct()

    new Postponed(
        callable $onDisposed = null
    )

Creates a new postponed object.

### Parameters
`$onDisposed`
:   A callback function to invoke when the emitter is disposed of.


## getEmitter()

    Postponed::getEmitter(): Emitter

Gets the contained [`Emitter`](Emitter.md) emitting the postponed values.


## emit()

    Postponed::emit($value = null): \Generator

Emits a value from the contained [`Emitter`](Emitter.md) object.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`$value`
:   If `$value` is an instance of `Icicle\Awaitable\Awaitable`, the fulfillment value is used as the value to emit or the rejection reason is thrown from this coroutine. If `$value` is an instance of `Generator`, it is used to create a coroutine which is then used as an awaitable.

### Resolution value
The emitted value (the resolution value of `$value`).

### Throws
`Icicle\Observable\Exception\CompletedError`
:   If the observable has been completed.

`Icicle\Observable\Exception\DisposedException`
:   If no listeners remain on the observable.


## complete()

    Postponed::complete($value = null)

Completes the observable with the given value.

### Parameters
`$value`
:   An optional value to comlete the observable with.


## fail()

    Postponed::fail(\Exception $reason)

Fails the observable with a given exception.

### Parameters
`$reason`
:   The exception to fail the observable with.
