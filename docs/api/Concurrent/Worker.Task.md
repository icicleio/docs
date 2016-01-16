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
