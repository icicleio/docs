A handle on an acquired lock from a synchronization object.

### isReleased()

    Lock::isReleased(): bool

Checks if the lock has already been released. Returns true if the lock has already been released, otherwise returns false.

### release()

    Lock::release(): void

Releases the lock to the mutex or semaphore that the lock was acquired from.
