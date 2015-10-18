## ChannelInterface
Interface for sending messages between execution contexts. A `ChannelInterface` object both acts as a sender and a receiver of messages.

### ChannelInterface::send()

    ChannelInterface::send(mixed $data): Generator

Sends a value across the channel to the receiver.

#### Parameters
`$data`
:   The data to send to the receiver. The value given must be serializable.

### ChannelInterface::receive()

    ChannelInterface::receive(): Generator

Receives the next pending value in the channel from the sender. Resolves with the received value.

---

## ContextInterface
Base interface for all types of execution contexts. Extends [`ChannelInterface`](#channelinterface).

### ContextInterface::isRunning()

    ContextInterface::isRunning(): bool

Checks if the context is currently running.

### ContextInterface::start()

    ContextInterface::start()

Starts the context execution.

### ContextInterface::join()

    ContextInterface::join(): Generator

Resolves when the context ends and joins with the parent context.

### ContextInterface::kill()

    ContextInterface::kill()

Forcefully kills the context.

---

## Forking\Fork
An execution context that uses forked processes. Implements [`Icicle\Concurrent\ContextInterface`](#contextinterface).

As forked processes are created with the [`pcntl_fork()`](http://php.net/pcntl_fork) function, the [PCNTL extension](http://php.net/manual/en/book.pcntl.php) must be enabled to spawn forks. Not compatible with Windows.

### Fork::spawn()

    static Fork::spawn(
        callable<(...$args): mixed> $function,
        ...$args
    ): Fork

Spawns a new forked process and immediately starts it. All arguments following the function to invoke in the fork will be copied and passed as parameters to the function to invoke.

`$function`
:   The function to invoke inside the forked process.

`...$args`
:   Arguments to pass to `$function`.

### Fork::getPid()

    Fork::getPid(): int

Gets the forked process's process ID.

### Fork::getPriority()

    Fork::getPriority(): float

Gets the fork's scheduling priority as a percentage.

The priority is a float between 0 and 1 that indicates the relative priority for the forked process, where 0 is very low priority, 1 is very high priority, and 0.5 is considered a "normal" priority. The value is based on the forked process's "nice" value. The priority affects the operating system's scheduling of processes. How much the priority actually affects the amount of CPU time the process gets is ultimately system-specific.

See also: [getpriority(2)](http://linux.die.net/man/2/getpriority)

### Fork::setPriority()

    Fork::setPriority(float $priority)

Sets the fork's scheduling priority as a percentage.

#### Parameters
`$priority`
:   A value between 0 and 1 indicating the relative priority to set.

!!! note
    On many systems, only the superuser can increase the priority of a process.

---

## Process\ChannelledProcess
An execution context that uses a separately executed PHP process. Implements [`Icicle\Concurrent\ContextInterface`](#contextinterface).

---

## Process\Process
An object for asynchronously spawning and managing an external process.

### Process::__construct()

    Process::__construct(string $command, string $cwd = '', array $env = [], array $options = [])

Creates a new process. The process will be run by executing the command specified by `$command`.

The working directory can be set by specifying `$cwd`. If `$cwd` is empty, the process will inherit the working directory of the current process.

Additional environment variables can be passed to the process by passing in an array of values to `$env`, where keys and values correspond to the names of the variables and their values.

Other options can be passed to [`proc_open()`](http://php.net/proc_open) in `$options`. See the documentation for [`proc_open()`](http://php.net/proc_open) for details.

### Process::start()

    Process::start()

Starts the process execution.

### Process::join()

    Process::join(): Generator

Resolves when the process ends.

### Process::kill()

    Process::kill()

Forcefully kills the process.

### Process::signal()

    Process::signal(int $signo)

Sends the given POSIX process signal to the running process.

#### Parameters
`$signo`
:   A POSIX signal number. Use constants such as `SIGTERM`, `SIGCHLD`, etc.

### Process::getPid()

    Process::getPid(): int

Returns the PID of the child process. Value is only meaningful if the process has been started and PHP was not compiled with ``--enable-sigchild`.

### Process::getCommand()

    Process::getCommand(): string

Gets the command to execute. Returns the current working directory or null if inherited from the current PHP process.

### Process::getWorkingDirectory()

Gets the current working directory.

### Process::getEnv()

    Process::getEnv(): array

Gets an associative array of environment variables passed to the process.

### Process::getOptions()

    Process::getOptions(): array

Gets the options to pass to [`proc_open()`](http://php.net/proc_open).

### Process::isRunning()

    Process::isRunning(): bool

Determines if the process is still running.

### Process::getStdIn()

    Process::getStdIn(): WritableStreamInterface

Gets the process input stream (STDIN).

### Process::getStdOut()

    Process::getStdIn(): ReadableStreamInterface

Gets the process output stream (STDERR).

### Process::getStdErr()

    Process::getStdIn(): ReadableStreamInterface

Gets the process error stream (STDOUT).

---

## Sync\Channel
An implementation of a standalone [`Icicle\Concurrent\Sync\ChannelInterface`](#syncchannelinterface) that uses a pair of streams.

### Channel::__construct()

    Channel::__construct(
        DuplexStreamInterface|ReadableStreamInterface $read,
        WritableStreamInterface $write = null
    )

Creates a new channel instance from one or two streams. Either a single [`DuplexStreamInterface`](stream.md#duplexstreaminterface) stream can be given, or a separate [`ReadableStreamInterface`](stream.md#readablestreaminterface) stream and [`WritableStreamInterface`](stream.md#writablestreaminterface) stream can be used.

---

## Sync\ChannelInterface
Interface for a standalone channel object that extends [`Icicle\Concurrent\ChannelInterface`](#channelinterface).

### ChannelInterface::isOpen()

    ChannelInterface::isOpen(): bool

Determines if the channel is open.

### ChannelInterface::close()

    ChannelInterface::close()

Closes the channel.

---

## Sync\FileMutex
A cross-platform mutex that implements [`Icicle\Concurrent\Sync\MutexInterface`](#syncmutexinterface) that uses exclusive files as the lock mechanism.

This mutex implementation is not always atomic and depends on the operating system's implementation of file creation operations. Use this implementation only if no other mutex types are available.

This implementation avoids using [flock()](http://php.net/flock) because flock() is known to have some atomicity issues on some systems. In addition, flock() does not work as expected when trying to lock a file multiple times in the same process on Linux. Instead, exclusive file creation is used to create a lock file, which is atomic on most systems.

---

## Sync\Lock
A handle on an acquired lock from a synchronization object.

### Lock::isReleased()

    Lock::isReleased(): bool

Checks if the lock has already been released. Returns true if the lock has already been released, otherwise returns false.

### Lock::release()

    Lock::release()

Releases the lock to the mutex or semaphore that the lock was acquired from.

---

## Sync\MutexInterface
A non-blocking synchronization primitive that can be used for mutual exclusion across contexts.

Objects that implement this interface should guarantee that all operations are atomic. Implementations do not have to guarantee that acquiring a lock is first-come, first serve.

### MutexInterface::acquire()

    SemaphoreInterface::acquire(): Generator

Acquires a lock on the mutex.

Resolves with a [`Icicle\Concurrent\Sync\Lock`](#synclock) object when the acquire is successful, which can be used to release the acquired lock.

---

## Sync\Parcel
A container object for sharing a value across contexts. Implements [`Icicle\Concurrent\Sync\ParcelInterface`](#syncparcelinterface).

A shared object is a container that stores an object inside shared memory. The object can be accessed and mutated by any thread or process. The shared object handle itself is serializable and can be sent to any thread or procss to give access to the value that is shared in the container.

Because each shared object uses its own shared memory segment, it is much more efficient to store a larger object containing many values inside a single shared container than to use many small shared containers.

When used with forking, the object must be created prior to forking for both processes to access the synchronized object.

Requires the [shmop](http://php.net/manual/en/book.shmop.php) extension to be enabled.

!!! note
    Accessing a shared object is not atomic. Access to a shared object should be protected with a mutex to preserve data integrity.

### Parcel::isFreed()

    Parcel::isFreed(): bool

Checks if the object has been freed.

Note that this does not check if the object has been destroyed; it only checks if this handle has freed its reference to the object.

### Parcel::free()

    Parcel::free()

Frees the shared object from memory. The memory containing the shared value will be invalidated. When all process disconnect from the object, the shared memory block will be destroyed by the OS.

Calling `free()` on an object already freed will have no effect. If this method is not called, the parcel will remain in memory until the system is restarted.

---

## Sync\ParcelInterface
A parcel object for sharing data across execution contexts.

A parcel is an object that stores a value in a safe way that can be shared between different threads or processes. Different handles to the same parcel will access the same data, and a parcel handle itself is serializable and can be transported to other execution contexts.

Wrapping and unwrapping values in the parcel are not atomic. To prevent race conditions and guarantee safety, you should use the provided synchronization methods to acquire a lock for exclusive access to the parcel first before accessing the contained value.

When a parcel is cloned, a new parcel is created and the original parcel's value is duplicated and copied to the new parcel.

### ParcelInterface::unwrap()

    ParcelInterface::unwrap(): mixed

Unwraps the parcel and returns the value inside the parcel.

### ParcelInterface::wrap()

    ParcelInterface::wrap(mixed $value)

Wraps a value into the parcel, replacing the old value.

#### Parameters
`$value`
:   The value to wrap into the parcel.

---

## Sync\PosixSemaphore
A non-blocking, interprocess POSIX semaphore that implements [`Icicle\Concurrent\Sync\SemaphoreInterface`](#syncsemaphoreinterface).

Uses a POSIX message queue to store a queue of permits in a lock-free data structure. This semaphore implementation is preferred over other implementations when available, as it provides the best performance.

!!! note
    Not compatible with Windows.

### PosixSemaphore::isFreed()

    PosixSemaphore::isFreed(): bool

Checks if the semaphore has been freed.

### PosixSemaphore::getPermissions()

    PosixSemaphore::getPermissions(): int

Gets the access permissions of the semaphore.

### PosixSemaphore::setPermissions()

    PosixSemaphore::setPermissions(int $mode)

Sets the access permissions of the semaphore.

#### Parameters
`$mode`
:   An octal representing the Unix permissions mode to set.

!!! note
    The current user must already have write access to the semaphore in order to change the semaphore's access permissions.

### PosixSemaphore::free()

    PosixSemaphore::free()

Removes the semaphore if it still exists. If this method is not called, the semaphore will remain in existence until the system is restarted.

---

## Sync\SemaphoreInterface
A non-blocking counting semaphore.

Objects that implement this interface guarantee that all operations are atomic. Implementations do not have to guarantee that acquiring a lock is first-come, first serve.

### SemaphoreInterface::count()

    SemaphoreInterface::count(): int

Gets the number of currently available locks.

### SemaphoreInterface::getSize()

    SemaphoreInterface::getSize(): int

Gets the total number of locks on the semaphore (not the number of available locks).

### SemaphoreInterface::acquire()

    SemaphoreInterface::acquire(): Generator

Acquires a lock from the semaphore asynchronously.

If there are one or more locks available, this function resolves immediately with a lock and the lock count is decreased. If no locks are available, the semaphore waits asynchronously for a lock to become available.

Resolves with a [`Icicle\Concurrent\Sync\Lock`](#synclock) object when the acquire is successful, which can be used to release the acquired lock.

---

## Threading\Mutex
A thread-safe, asynchronous mutex that implements [`Icicle\Concurrent\Sync\MutexInterface`](#syncmutexinterface) using the pthreads locking mechanism.

Compatible with POSIX systems and Microsoft Windows.

---

## Threading\Parcel
A thread-safe container that shares a value between multiple threads. Implements [`Icicle\Concurrent\Sync\ParcelInterface`](#syncparcelinterface).

This parcel implementation is preferred when sharing objects between threads.

---

## Threading\Semaphore
An asynchronous semaphore based on pthreads' synchronization methods. Implements [`Icicle\Concurrent\Sync\SemaphoreInterface`](#syncsemaphoreinterface).

This is an implementation of a thread-safe semaphore that has non-blocking acquire methods. There is a small tradeoff for asynchronous semaphores; you may not acquire a lock immediately when one is available and there may be a small delay. However, the small delay will not block the thread.

---

## Threading\Thread
An execution context using native multi-threading. Implements [`Icicle\Concurrent\ContextInterface`](#contextinterface).

The thread context is not itself threaded. A local instance of the context is maintained both in the context that creates the thread and in the thread itself.

### Thread::spawn()

```php
Thread::spawn(
    callable<(...$args): mixed> $function,
    ...$args
): Thread
```

Creates a new thread and immediately starts it. All arguments following the function to invoke in the thread will be copied and passed as parameters to the function to invoke.

`$function`
:   The function to invoke inside the new thread.

`...$args`
:   Arguments to pass to `$function`.

!!! warning
    Due to the underlying process of passing a closure to another thread, using a closure for `$function` that [imports variables](http://php.net/manual/en/functions.anonymous.php#example-195) from a scope in the parent thread can cause malformed internal pointers. Attempting to pass such a function will result in an `InvalidArgumentError` being thrown.

Example:

```php
$thread = Thread::spawn(function ($value) {
    echo $value === 42 ? 'true' : 'false';
}, 42);
```
