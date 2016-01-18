Base interface for all types of execution contexts.


### isRunning()

    Context::isRunning(): bool

Checks if the context is currently running.


### start()

    Context::start(): void

Starts the context execution.


### join()

    Context::join(): \Generator

Resolves when the context ends and joins with the parent context.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.


### kill()

    Context::kill(): void

Forcefully kills the context.
