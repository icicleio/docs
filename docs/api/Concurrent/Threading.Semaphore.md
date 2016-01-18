An asynchronous semaphore based on pthreads' synchronization methods.

**Implements**
:   [`Sync\Semaphore`](Sync.Semaphore.md)

This is an implementation of a thread-safe semaphore that has non-blocking acquire methods. There is a small tradeoff for asynchronous semaphores; you may not acquire a lock immediately when one is available and there may be a small delay. However, the small delay will not block the thread.


## __construct()

    new Semaphore(int $locks)

Creates a new semaphore with a given number of locks.

### Parameters
`$locks`
:   The maximum number of locks that can be acquired from the semaphore.
