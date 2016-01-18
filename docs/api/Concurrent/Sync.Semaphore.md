A non-blocking counting semaphore.

**Extends**
:   [`Countable`](http://php.net/Countable)

Objects that implement this interface guarantee that all operations are atomic. Implementations do not have to guarantee that acquiring a lock is first-come, first serve.


## count()

    Semaphore::count(): int

Gets the number of currently available locks.


## getSize()

    Semaphore::getSize(): int

Gets the total number of locks on the semaphore (not the number of available locks).


## acquire()

    Semaphore::acquire(): \Generator

Acquires a lock from the semaphore asynchronously.

If there are one or more locks available, this function resolves immediately with a lock and the lock count is decreased. If no locks are available, the semaphore waits asynchronously for a lock to become available.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Resolution value
`Icicle\Concurrent\Sync\Lock`
:   Lock object which can be used to release the acquired.
