A cross-platform mutex that implements [`Icicle\Concurrent\Sync\Mutex`](Sync.Mutex.md) that uses exclusive files as the lock mechanism.

This implementation avoids using [`flock()`](http://php.net/flock) because `flock()` is known to have some atomicity issues on some systems. In addition, `flock()` does not work as expected when trying to lock a file multiple times in the same process on Linux. Instead, exclusive file creation is used to create a lock file, which is atomic on most systems.

!!! note
    This mutex implementation is not always atomic and depends on the operating system's implementation of file creation operations. Use this implementation only if no other mutex types are available.
