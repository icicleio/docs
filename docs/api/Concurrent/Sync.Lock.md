A handle on an acquired lock from a synchronization object.

!!! warning
    This object is not thread-safe; after acquiring a lock from a mutex or semaphore, the lock must reside in the same thread or process until it is released.


## __construct()

    new Lock(callable(Lock $lock) $releaser)

Creates a new lock permit object.

### Parameters
`$releaser`
:   A function to be called upon release.


## isReleased()

    Lock::isReleased(): bool

Checks if the lock has already been released. Returns true if the lock has already been released, otherwise returns false.

### Return value
True if the lock has already been released, otherwise false.


## release()

    Lock::release(): void

Releases the lock to the mutex or semaphore that the lock was acquired from.

### Throws
`Icicle\Concurrent\Exception\LockAlreadyReleasedError`
:   Thrown if the lock was already released.
