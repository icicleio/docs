An interface for a parallel worker thread that runs a queue of tasks.


## isRunning()

    Worker::isRunning(): bool

Checks if the worker is running.

### Return value
True if the worker is running, otherwise false.


## isIdle()

    Worker::isIdle(): bool

Checks if the worker is currently idle.

### Return value
True if the worker is idle, otherwise false.


## start()

    Worker::start()

Starts the context execution.


## enqueue()

    Worker::enqueue(Task $task): \Generator<mixed>

Enqueues a task to be executed by the worker.

### Parameters
`Task $task`
:   The task to enqueue.

### Return value
Generator that resolves with the task return value.


## shutdown()

    Worker::shutdown(): \Generator<int>

### Return value
Generator that resolves with the underlaying context's exit code.


## kill()

    Worker::kill()

Immediately kills the worker and the underlaying context.
