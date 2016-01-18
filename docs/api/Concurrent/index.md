This optional package provides native threading, multiprocessing, process synchronization, shared memory, and task workers. The package is available on Packagist as `icicleio/concurrent`.


## Worker\pool()

    Worker\pool(Pool $pool = null): Pool

Returns the global worker pool for the current context.

### Parameters
`$pool`
:   A worker pool instance.

### Return value
The global worker pool instance.


## Worker\enqueue()

    Worker\enqueue(Task $task): \Generator

Enqueues a task to be executed by the global worker pool.

### Parameters
`Task $task`
:   The task to enqueue.

### Return value
Generator that resolves with the task return value.


## Worker\create()

    Worker\create(): Worker

Creates a worker using the global worker factory.


## Worker\factory()

    Worker\factory(WorkerFactory $factory = null): WorkerFactory

Gets or sets the global worker factory.


## Worker\get()

    Worker\get(): Worker

Gets a worker from the global worker pool.
