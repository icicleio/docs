A runnable unit of execution.


## run()

    Task::run(Environment $environment): mixed

Runs the task inside the caller's context.

Does not have to be a coroutine, can also be a regular function returning a value.

### Parameters
`Environment $environment`
:   The worker environment that the task is being run in.

### Return value
Any return value that should be passed to the caller running the task.
