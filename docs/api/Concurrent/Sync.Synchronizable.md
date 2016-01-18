An object that can be synchronized for exclusive access across contexts.


## synchronized()

    Synchronizable::synchronized(
        callable(mixed ...$args): \Generator|mixed $callback
    ): \Generator

Asynchronously invokes a callback while maintaining an exclusive lock on the object.

The arguments passed to the callback depend on the implementing object. If the callback throws an exception, the lock on the object will be immediately released.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`$callback`
:   The synchronized callback to invoke. The callback may be a regular function or a coroutine.

#### Resolve
`mixed`
:   The return value of `$callback`.
