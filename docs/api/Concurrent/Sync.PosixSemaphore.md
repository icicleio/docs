A non-blocking, interprocess POSIX semaphore that implements [`Icicle\Concurrent\Sync\Semaphore`](Sync.Semaphore.md).

Uses a POSIX message queue to store a queue of permits in a lock-free data structure. This semaphore implementation is preferred over other implementations when available, as it provides the best performance.

!!! note
    Not compatible with Windows.

### isFreed()

    PosixSemaphore::isFreed(): bool

Checks if the semaphore has been freed.

### getPermissions()

    PosixSemaphore::getPermissions(): int

Gets the access permissions of the semaphore.

### setPermissions()

    PosixSemaphore::setPermissions(int $mode): void

Sets the access permissions of the semaphore.

#### Parameters
`int $mode`
:   An octal representing the Unix permissions mode to set.

!!! note
    The current user must already have write access to the semaphore in order to change the semaphore's access permissions.

### free()

    PosixSemaphore::free()

Removes the semaphore if it still exists. If this method is not called, the semaphore will remain in existence until the system is restarted.
