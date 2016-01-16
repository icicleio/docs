### Coroutine Constructor

    $coroutine = new Coroutine(\Generator $generator)

A `Icicle\Coroutine\Coroutine` instance can be created by passing a `\Generator` instance to the constructor. The coroutine constructor is often used when you wish to create an awaitable object from a function or method returning a `\Generator` written to be a coroutine (noted with a box in these docs or `@coroutine` in docblocks within the source).


### pause()

    Coroutine::pause(): void

Pauses the coroutine once it reaches a `yield` statement (if executing). If the coroutine was already at a `yield` statement (or has not begun execution), no further code will be executed until resumed with `resume()`. Any awaitables that the coroutine is currently waiting for will continue to do work to be resolved, but once resolved, the coroutine will not continue until resumed.


### resume()

    Coroutine::resume(): void

Resumes the coroutine if it was paused. If the coroutine was waiting for an awaitable to resolve, the coroutine will not continue execution until the awaitable has resolved.


### isPaused()

    Coroutine::isPaused(): bool

Determines if the coroutine is currently paused. Note that true is only returned if the coroutine was explicitly paused. It does not return true if the coroutine is waiting for an awaitable to resolve.

#### Return value
A boolean indicating if the coroutine is currently paused.


### cancel()

    Coroutine::cancel(\Exception $reason = null): void

Cancels execution of the coroutine. If the coroutine is waiting on an awaitable, that awaitable is cancelled with the given exception.

#### Parameters
`$reason`
:   An exception to cancel the coroutine with. If no exception is given, an instance of `Icicle\Coroutine\Exception\TerminatedException` will be used.
