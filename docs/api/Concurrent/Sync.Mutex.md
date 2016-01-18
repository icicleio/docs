A non-blocking synchronization primitive that can be used for mutual exclusion across contexts.

Objects that implement this interface should guarantee that all operations are atomic. Implementations do not have to guarantee that acquiring a lock is first-come, first serve.


## acquire()

    Mutex::acquire(): \Generator

Acquires a lock on the mutex.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Resolution value
`Icicle\Concurrent\Sync\Lock`
:   Lock object which can be used to release the acquired.
