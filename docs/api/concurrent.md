This optional package provides native threading, multiprocessing, process synchronization, shared memory, and task workers. The package is available on [Packagist](https://packagist.org) as [`icicleio/concurrent`](https://packagist.org/packages/icicleio/concurrent).


## Channel

Interface for sending messages between execution contexts. A `Icicle\Concurrent\Sync\Channel` object both acts as a sender and a receiver of messages.

### send()

    Channel::send(mixed $data): \Generator

Sends a value across the channel to the receiver.

!!! note
    [**Coroutine**](../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`mixed $data`
:   The data to send to the receiver. The value given must be serializable.

#### Resolution value
`int`
:   The number of bytes written to the stream to send the value.

### receive()

    Channel::receive(): \Generator

Receives the next pending value in the channel from the sender. Resolves with the received value.

!!! note
    [**Coroutine**](../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Resolution value
`mixed`
:   The data received.


## Context

Base interface for all types of execution contexts.

### isRunning()

    Context::isRunning(): bool

Checks if the context is currently running.

### start()

    start(): void

Starts the context execution.

### join()

    Context::join(): \Generator

Resolves when the context ends and joins with the parent context.

!!! note
    [**Coroutine**](../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### kill()

    Context::kill(): void

Forcefully kills the context.


## Strand

Execution context that includes a channel for exchanging data. Extends both [`Channel`](#channel) and [`Context`](#context).


## Forking\Fork
An execution context that uses forked processes. Implements [`Icicle\Concurrent\Context`](#context).

As forked processes are created with the [`pcntl_fork()`](http://php.net/pcntl_fork) function, the [PCNTL extension](http://php.net/manual/en/book.pcntl.php) must be enabled to spawn forks. Not compatible with Windows.

### Fork::spawn()

    static Fork::spawn(
        callable(...$args): mixed $function,
        ...$args
    ): Fork

Spawns a new forked process and immediately starts it. All arguments following the function to invoke in the fork will be copied and passed as parameters to the function to invoke.

#### Parameters
`callable(...$args): mixed $function`
:   The function to invoke inside the forked process.

`mixed ...$args`
:   Arguments to pass to `$function`.

### getPid()

    Fork::getPid(): int

Gets the forked process's process ID.

### getPriority()

    Fork::getPriority(): float

Gets the fork's scheduling priority as a percentage.

The priority is a float between 0 and 1 that indicates the relative priority for the forked process, where 0 is very low priority, 1 is very high priority, and 0.5 is considered a "normal" priority. The value is based on the forked process's "nice" value. The priority affects the operating system's scheduling of processes. How much the priority actually affects the amount of CPU time the process gets is ultimately system-specific.

See also: [getpriority(2)](http://linux.die.net/man/2/getpriority)

### setPriority()

    Fork::setPriority(float $priority)

Sets the fork's scheduling priority as a percentage.

#### Parameters
`int $priority`
:   A value between 0 and 1 indicating the relative priority to set.

!!! note
    On many systems, only the superuser can increase the priority of a process.


## Process\ChannelledProcess

An execution context that uses a separately executed PHP process. Implements [`Icicle\Concurrent\Strand`](#strand).


## Process\Process

An object for asynchronously spawning and managing an external process.

### Process::__construct()

    Process::__construct(string $command, string $cwd = '', array $env = [], array $options = [])

Creates a new process. The process will be run by executing the command specified by `$command`.

The working directory can be set by specifying `$cwd`. If `$cwd` is empty, the process will inherit the working directory of the current process.

Additional environment variables can be passed to the process by passing in an array of values to `$env`, where keys and values correspond to the names of the variables and their values.

Other options can be passed to [`proc_open()`](http://php.net/proc_open) in `$options`. See the documentation for [`proc_open()`](http://php.net/proc_open) for details.

### start()

    Process::start(): void

Starts the process execution.

### join()

    Process::join(): \Generator

Resolves when the process ends.

!!! note
    [**Coroutine**](../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### kill()

    Process::kill(): void

Forcefully kills the process.

### signal()

    Process::signal(int $signo): void

Sends the given POSIX process signal to the running process.

#### Parameters
`int $signo`
:   A POSIX signal number. Use constants such as `SIGTERM`, `SIGCHLD`, etc.

### getPid()

    Process::getPid(): int

Returns the PID of the child process. Value is only meaningful if the process has been started and PHP was not compiled with ``--enable-sigchild`.

### getCommand()

    Process::getCommand(): string

Gets the command to execute. Returns the current working directory or null if inherited from the current PHP process.

### getWorkingDirectory()

Gets the current working directory.

### getEnv()

    Process::getEnv(): array

Gets an associative array of environment variables passed to the process.

### getOptions()

    Process::getOptions(): array

Gets the options to pass to [`proc_open()`](http://php.net/proc_open).

### isRunning()

    Process::isRunning(): bool

Determines if the process is still running.

### getStdIn()

    Process::getStdIn(): Icicle\Stream\WritableStream

Gets the process input stream (STDIN).

### getStdOut()

    Process::getStdIn(): Icicle\Stream\ReadableStream

Gets the process output stream (STDERR).

### getStdErr()

    Process::getStdIn(): Icicle\Stream\ReadableStream

Gets the process error stream (STDOUT).


## Sync\ChannelledStream
An implementation of a standalone [`Icicle\Concurrent\Sync\Channel`](#channel) that uses a pair of streams.

### ChannelledStream::__construct()

    ChannelledStream::__construct(
        DuplexStream|ReadableStream $read,
        WritableStream|null $write = null
    )

Creates a new channel instance from one or two streams. Either a single [`DuplexStream`](stream.md#duplexstream) stream can be given, or a separate [`ReadableStream`](stream.md#readablestream) stream and [`WritableStream`](stream.md#writablestream) stream can be used.

#### Parameters
`Icicle\Stream\DuplexStream|Icicle\Stream\ReadableStream $read`
:   The single duplex stream instance or the readable stream to use for the channel.

`Icicle\Stream\WritableStream|null $write`
:   The writable stream to use for the channel if `$read` was only a readable stream.

### isOpen()

    ChannelledStream::isOpen(): bool

Determines if the channel is open.

### close()

    ChannelledStream::close()

Closes the channel.


## Sync\FileMutex
A cross-platform mutex that implements [`Icicle\Concurrent\Sync\Mutex`](#syncmutex) that uses exclusive files as the lock mechanism.

This implementation avoids using [`flock()`](http://php.net/flock) because `flock()` is known to have some atomicity issues on some systems. In addition, `flock()` does not work as expected when trying to lock a file multiple times in the same process on Linux. Instead, exclusive file creation is used to create a lock file, which is atomic on most systems.

!!! note
    This mutex implementation is not always atomic and depends on the operating system's implementation of file creation operations. Use this implementation only if no other mutex types are available.


## Sync\Lock
A handle on an acquired lock from a synchronization object.

### isReleased()

    Lock::isReleased(): bool

Checks if the lock has already been released. Returns true if the lock has already been released, otherwise returns false.

### release()

    Lock::release(): void

Releases the lock to the mutex or semaphore that the lock was acquired from.


## Sync\Mutex
A non-blocking synchronization primitive that can be used for mutual exclusion across contexts.

Objects that implement this interface should guarantee that all operations are atomic. Implementations do not have to guarantee that acquiring a lock is first-come, first serve.

### acquire()

    Mutex::acquire(): \Generator

Acquires a lock on the mutex.

!!! note
    [**Coroutine**](../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Resolution value
`Icicle\Concurrent\Sync\Lock`
:   Lock object which can be used to release the acquired.


## Sync\Parcel
A container object for sharing a value across contexts. Implements [`Icicle\Concurrent\Sync\Parcel`](#syncparcel).

A shared object is a container that stores an object inside shared memory. The object can be accessed and mutated by any thread or process. The shared object handle itself is serializable and can be sent to any thread or procss to give access to the value that is shared in the container.

Because each shared object uses its own shared memory segment, it is much more efficient to store a larger object containing many values inside a single shared container than to use many small shared containers.

When used with forking, the object must be created prior to forking for both processes to access the synchronized object.

Requires the [shmop](http://php.net/manual/en/book.shmop.php) extension to be enabled.

!!! note
    Accessing a shared object is not atomic. Access to a shared object should be protected with a mutex to preserve data integrity.

### isFreed()

    Parcel::isFreed(): bool

Checks if the object has been freed.

Note that this does not check if the object has been destroyed; it only checks if this handle has freed its reference to the object.

### free()

    Parcel::free()

Frees the shared object from memory. The memory containing the shared value will be invalidated. When all process disconnect from the object, the shared memory block will be destroyed by the OS.

Calling `free()` on an object already freed will have no effect. If this method is not called, the parcel will remain in memory until the system is restarted.


## Sync\Parcel
A parcel object for sharing data across execution contexts.

A parcel is an object that stores a value in a safe way that can be shared between different threads or processes. Different handles to the same parcel will access the same data, and a parcel handle itself is serializable and can be transported to other execution contexts.

Wrapping and unwrapping values in the parcel are not atomic. To prevent race conditions and guarantee safety, you should use the provided synchronization methods to acquire a lock for exclusive access to the parcel first before accessing the contained value.

When a parcel is cloned, a new parcel is created and the original parcel's value is duplicated and copied to the new parcel.

### unwrap()

    Parcel::unwrap(): mixed

Unwraps the parcel and returns the value inside the parcel.

### synchronized()

    Parcel::synchronized(callable(mixed $value): mixed $function): \Generator

Calls the given callback function while maintaining a lock on the parcel so only one thread may modify the value of the parcel. The current value of the parcel is given to the callback function and the function should return the new value to be stored in the parcel.

!!! note
    [**Coroutine**](../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`callable(mixed $value): mixed $function`
:   The callback function to be invoked. This function is given the current parcel value as the parameter and should return the new value to store in the parcel.

#### Resolve
`mixed`
:   The new parcel value.


## Sync\PosixSemaphore
A non-blocking, interprocess POSIX semaphore that implements [`Icicle\Concurrent\Sync\Semaphore`](#syncsemaphoreinterface).

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


## Sync\Semaphore
A non-blocking counting semaphore.

Objects that implement this interface guarantee that all operations are atomic. Implementations do not have to guarantee that acquiring a lock is first-come, first serve.

### count()

    Semaphore::count(): int

Gets the number of currently available locks.

### getSize()

    Semaphore::getSize(): int

Gets the total number of locks on the semaphore (not the number of available locks).

### acquire()

    Semaphore::acquire(): \Generator

Acquires a lock from the semaphore asynchronously.

If there are one or more locks available, this function resolves immediately with a lock and the lock count is decreased. If no locks are available, the semaphore waits asynchronously for a lock to become available.

!!! note
    [**Coroutine**](../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Resolution value
`Icicle\Concurrent\Sync\Lock`
:   Lock object which can be used to release the acquired.


## Threading\Mutex
A thread-safe, asynchronous mutex that implements [`Icicle\Concurrent\Sync\Mutex`](#syncmutexinterface) using the pthreads locking mechanism.

Compatible with POSIX systems and Microsoft Windows.


## Threading\Parcel
A thread-safe container that shares a value between multiple threads. Implements [`Icicle\Concurrent\Sync\Parcel`](#syncparcelinterface).

This parcel implementation is preferred when sharing objects between threads.


## Threading\Semaphore
An asynchronous semaphore based on pthreads' synchronization methods. Implements [`Icicle\Concurrent\Sync\Semaphore`](#syncsemaphoreinterface).

This is an implementation of a thread-safe semaphore that has non-blocking acquire methods. There is a small tradeoff for asynchronous semaphores; you may not acquire a lock immediately when one is available and there may be a small delay. However, the small delay will not block the thread.


## Threading\Thread
An execution context using native multi-threading. Implements [`Icicle\Concurrent\Context`](#contextinterface).

The thread context is not itself threaded. A local instance of the context is maintained both in the context that creates the thread and in the thread itself.

### Thread::spawn()

```php
Thread::spawn(
    callable(...$args): mixed $function,
    ...$args
): Thread
```

Creates a new thread and immediately starts it. All arguments following the function to invoke in the thread will be copied and passed as parameters to the function to invoke.

#### Parameters
`callable(...$args): mixed $function`
:   The function to invoke inside the new thread.

`mixed ...$args`
:   Arguments to pass to `$function`.

!!! warning
    Due to the underlying process of passing a closure to another thread, using a closure for `$function` that [imports variables](http://php.net/manual/en/functions.anonymous.php#example-195) from a scope in the parent thread can cause malformed internal pointers. Attempting to pass such a function will result in an `Icicle\Exception\InvalidArgumentError` being thrown.

Example:

```php
$thread = Thread::spawn(function ($value) {
    echo $value === 42 ? 'true' : 'false';
}, 42);
```


## Worker\AbstractWorker
Base class for most common types of task workers.


## Worker\DefaultPool
The default [Worker\Pool](#workerpool) implementation.

### Constructor

    DefaultPool::__construct(
        int $minSize = null,
        int $maxSize = null,
        WorkerFactory $factory = null
    )

Creates a new worker pool.

#### Parameters
`int|null $minSize`
:   The minimum number of workers the pool should spawn. Defaults to `Pool::DEFAULT_MIN_SIZE`.

`int|null $maxSize`
:   The maximum number of workers the pool should spawn. Defaults to `Pool::DEFAULT_MAX_SIZE`.

`\Icicle\Concurrent\Worker\WorkerFactory|null $factory`
:   A worker factory to be used to create new workers.


## Worker\DefaultQueue
The default [`Worker\Queue`](#workerqueue) implementation.

### Constructor

    DefaultPool::__construct(
        int $minSize = null,
        int $maxSize = null,
        WorkerFactory $factory = null
    )

Creates a new worker pool.

#### Parameters
`int|null $minSize`
:   The minimum number of workers the pool should spawn. Defaults to `Pool::DEFAULT_MIN_SIZE`.

`int|null $maxSize`
:   The maximum number of workers the pool should spawn. Defaults to `Pool::DEFAULT_MAX_SIZE`.

`\Icicle\Concurrent\Worker\WorkerFactory|null $factory`
:   A worker factory to be used to create new workers.


## Worker\DefaultWorkerFactory
The built-in [`Worker\WorkerFactory`](#workerworkerfactory) type.

The type of worker created by this factory depends on the extensions available. If multi-threading is enabled, a `WorkerThread` will be created. If threads are not available, a `WorkerFork` will be created if forking is available, otherwise a `WorkerProcess` will be created.


## Worker\Environment
`implements \ArrayAccess, \Countable`

A persistent object storage type provided by a worker.

When a worker is created, it initializes a new environment object, which is stored in memory that is local to that worker. When a worker executes a task, this persistent environment object is given to the task to use.

An environment is not destroyed until the worker that owns it is shut down.


### exists()

    Environment::exists(string $key): bool

Checks if a given key exists.

#### Parameters
`string $key`
:   The key to check.

#### Return value
True if the key exists, otherwise false.


### get()

    Environment::get(string $key): mixed|null

#### Parameters
`string $key`
:   The key to get.

#### Return value
The value stored for the given key, or `null` if the key does not exist.


### set()

    Environment::set(string $key, mixed $value, int $ttl = 0)

Sets a key/value pair in the environment.

#### Parameters
`string $key`
:   The key to set.

`mixed $value`
:   The value to set.

`int $ttl`
:   Number of seconds until data is automatically deleted. Use 0 for unlimited TTL.


### delete()

    Environment::delete(string $key)

Deletes a value based on its key.

#### Parameters
`string $key`
:   The key to delete.


### count()

    Environment::count(): int

Gets the number of values in the environment.


### clear()

    Environment::clear()

Removes all values.


## Worker\Pool
A pool of workers that can be used to execute multiple tasks synchronously.

A worker pool is a collection of worker threads that can perform multiple tasks simultaneously. The load on each worker is balanced such that tasks are completed as soon as possible and workers are used efficiently.


### getWorkerCount()

    Pool::getWorkerCount(): int

Gets the number of workers currently running in the pool.

#### Return value
The number of workers.


### getIdleWorkerCount()

    Pool::getIdleWorkerCount(): int

Gets the number of workers that are currently idle.

#### Return value
The number of idle workers.


### getMinSize()

    Pool::getMinSize(): int

Gets the minimum number of workers the pool may have idle.

#### Return value
The minimum number of workers.


### getMaxSize()

    Pool::getMaxSize(): int

Gets the maximum number of workers the pool may spawn to handle concurrent tasks.

#### Return value
The maximum number of workers.


## Worker\Queue

### pull()

    Queue::pull(): Worker

Pulls a worker from the queue. The worker is marked as busy and will only be reused if the queue runs out of idle workers.

#### Exceptions
`Icicle\Concurrent\Exception\StatusError`
:   If the queue is not running.

#### Return value
A worker pulled from the queue.


### push()

    Queue::push(Worker $worker)

Pushes a worker into the queue, marking it as idle and available to be pulled from the queue again.

#### Parameters
`Icicle\Concurrent\Worker\Worker $worker`
:   The worker to push.

#### Exceptions
`Icicle\Concurrent\Exception\StatusError`
:   If the queue is not running.

`Icicle\Exception\InvalidArgumentError`
:   If the given worker is not part of this queue or was already pushed into the queue.


### getWorkerCount()

    Pool::getWorkerCount(): int

Gets the number of workers currently running in the queue.

#### Return value
The number of workers.


### getIdleWorkerCount()

    Pool::getIdleWorkerCount(): int

Gets the number of workers that are currently idle.

#### Return value
The number of idle workers.


### getMinSize()

    Pool::getMinSize(): int

Gets the minimum number of workers the queue may have idle.

#### Return value
The minimum number of workers.


### getMaxSize()

    Pool::getMaxSize(): int

Gets the maximum number of workers the queue may spawn to handle concurrent tasks.

#### Return value
The maximum number of workers.


## Worker\Task
A runnable unit of execution.

### Task::run()

    Task::run(Environment $environment): mixed

Runs the task inside the caller's context.

Does not have to be a coroutine, can also be a regular function returning a value.

#### Parameters
`Environment $environment`
:   The worker environment that the task is being run in.

#### Return value
Any return value that should be passed to the caller running the task.


## Worker\Worker
An interface for a parallel worker thread that runs a queue of tasks.

### isRunning()

    Worker::isRunning(): bool

Checks if the worker is running.

#### Return value
True if the worker is running, otherwise false.


### isIdle()

    Worker::isIdle(): bool

Checks if the worker is currently idle.

#### Return value
True if the worker is idle, otherwise false.


### start()

    Worker::start()

Starts the context execution.


### enqueue()

    Worker::enqueue(Task $task): \Generator<mixed>

Enqueues a task to be executed by the worker.

#### Parameters
`Task $task`
:   The task to enqueue.

#### Return value
Generator that resolves with the task return value.


### shutdown()

    Worker::shutdown(): \Generator<int>

#### Return value
Generator that resolves with the underlaying context's exit code.


### kill()

    Worker::kill()

Immediately kills the worker and the underlaying context.


## Worker\WorkerFactory
Interface for factories used to create new workers.

### create()

    WorkerFactory::create(): Worker

Creates a new worker instance.

#### Return value
The newly created worker.


## Worker\WorkerFork
Implements the [`Worker\Worker`](#workerworker) interface.

A forked process that executes task objects.


## Worker\WorkerProcess
Implements the [`Worker\Worker`](#workerworker) interface.

A PHP process that executes task objects.


## Worker\WorkerThread
Implements the [`Worker\Worker`](#workerworker) interface.

A worker thread that executes task objects.
