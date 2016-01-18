The Loop component implements an event loop that is used to schedule functions, run timers, handle signals, and poll sockets.

Any asynchronous code needs to use the same event loop to be interoperable and non-blocking. The loop component provides the a set of functions in the `Icicle\Loop` namespace that should be used to access the single active event loop. These function act as a container for an instance of `Icicle\Loop\Loop` that actually implements the event loop.

The functions available in `Icicle\Loop` (abbreviated to `Loop` in the function prototypes) are described below.


## loop()

    Icicle\Loop\loop(
        Icicle\Loop\Loop $loop = null
    ): Icicle\Loop\Loop

This function accesses the active event loop or, if called with an instance of `Icicle\Loop\Loop`, allows any particular or custom implementation of `Icicle\Loop\Loop` to be used as the event loop. If specifying an event loop, this function should be called before any code that would access the event loop, otherwise a `Icicle\Loop\Exception\InitializedException` will be thrown since the default factory would have already created an event loop.

### Parameters
`Icicle\Loop\Loop $loop`
:   An event loop instance to use as the global event loop. To use the default created loop, set as `null`.

### Return value
`Icicle\Loop\Loop`
:   The global event loop instance. If an instance was given for `$loop`, the returned loop will be the same instance as `$loop`.


## create()

    Icicle\Loop\create(bool $enableSignals = true): Icicle\Loop\Loop

Creates a new event loop instance using the best implementation available. Available implementations depends on the configured and enabled extensions. If no extensions are available, a `Icicle\Loop\SelectLoop` instance will be created. See [loop implementations](../../manual/loop.md#loop-implementations) for details.

### Parameters
`bool $enableSignals`
:   By default, process signal handling will be enabled in the created event loop. To disable signal handling, set this to `false`.

### Return value
`Icicle\Loop\Loop`
:   The new event loop instance.


## run()

    Icicle\Loop\run(callable $initialize = null): bool

Runs the event loop until there are no events pending in the loop.

This function is generally the last line in the script that starts the program, as this function blocks until there are no pending events.

### Parameters
`callable(): mixed|null $initialize`
:   An optional initialization function that is called as soon as the loop is started. This function can be used to create a set of initial events or set up a server.

### Return value
`bool`
:   Returns `true` if the loop exited because `stop()` was called or `false` if the loop exited because there were no more pending events.


## with()

    Icicle\Loop\with(
        callable(): mixed $worker,
        Icicle\Loop\Loop|null $loop = null
    ): bool

Runs the tasks set up in the given `$worker` function in a separate, specified event loop from the default event loop. If the default event loop is currently running, it will be blocked while the separate event loop runs.

### Parameters
`callable(): mixed $worker`
:   The function that should be invoked using a separate event loop. Calling [`loop()`](#loop) inside this function will return the overriden event loop.

`Icicle\Loop\Loop|null $loop = null`
: The loop to run the tasks created by `$worker` in. If no loop is specified, a new loop instance will be automatically created and used.

### Return value
`bool`
:   Returns `true` if the loop exited because `stop()` was called or `false` if the loop exited because there were no more pending events.


## queue()

    Icicle\Loop\queue(
        callable(mixed ...$args): mixed $callback,
        mixed ...$args
    ): void

Queues a function to be executed later. The function may be executed as soon as immediately after the calling scope exits. Functions are guaranteed to be executed in the order queued. This function is useful for ensuring that functions are called asynchronously.

!!! note
    This function is used internally by Icicle to ensure tasks are executed asynchronously and do not blow up the call stack, but is generally unnecessary to use this function elsewhere.

### Parameters
`callable(mixed ...$args): mixed$callback`
:   The callback function to queue.

`mixed ...$args`
:   Parameters to pass to the callback function.


## tick()

    Icicle\Loop\tick(bool $blocking = false): void

Executes a single turn of the event loop.

### Parameters
`bool $blocking = false`
:   Set to `true` to block until at least one pending event becomes active, or set to `false` to return immediately, even if no events are executed.


## isRunning()

    Icicle\Loop\isRunning(): bool

Determines if the loop is currently running.

### Return value
`bool`
:   A boolean indicating if the loop is running.


## stop()

    Icicle\Loop\stop(): void

Stops the loop if it is running.


## maxQueueDepth()

    Icicle\Loop\maxQueueDepth(int $depth): int

Sets the maximum number of callbacks set with [`queue()`](#queue) that will be executed per tick.

### Parameters
`int $depth`
:   Maximum number of functions to execute each tick. Use 0 for unlimited.

### Return value
`int`
:   The previous max schedule depth.


## poll()

    Icicle\Loop\poll(
        resource $resource,
        callable(
            resource $resource,
            bool $expired,
            Icicle\Loop\Watcher\Io
        ): void $callback,
        bool $persistent = false
    ): Icicle\Loop\Watcher\Io

Creates an [`Icicle\Loop\Watcher\Io`](Watcher.Io.md) object for the given stream socket that will listen for data to become available on the stream.

### Parameters
`resource $resource`
:   A stream socket resource to poll.

`callable(resource $resource, bool $expired, Icicle\Loop\Watcher\Io $io): void $callback`
:   Callback to be invoked when data is available on the stream.

`bool $persistent = false`
:   If `true`, calling `listen()` on the returned `Io` watcher will continue listening until `cancel()` is called on the watcher.

### Return value
`Icicle\Loop\Watcher\Io`
:   Watcher object for polling the socket for data.


## await()

    Icicle\Loop\await(
        resource $resource,
        callable(
            resource $resource,
            bool $expired,
            Icicle\Loop\Watcher\Io
        ): void $callback,
        bool $persistent = false
    ): Icicle\Loop\Watcher\Io

Creates an [`Icicle\Loop\Watcher\Io`](Watcher.Io.md) object for the given stream socket that will listen for the ability to write to the stream.

### Parameters
`resource $resource`
:   A stream socket resource to await.

`callable(resource $resource, bool $expired, Icicle\Loop\Watcher\Io $io): void $callback`
:   Callback to be invoked when the stream is available to write.

`bool $persistent = false`
:   If `true`, calling `listen()` on the returned `Io` watcher will continue listening until `cancel()` is called on the watcher.

### Return value
`Icicle\Loop\Watcher\Io`
:   Watcher object for awaiting space to write.


## timer()

    Icicle\Loop\timer(
        float $interval,
        callable(Icicle\Loop\Watcher\Timer $timer): void $callback,
    ): Icicle\Loop\Watcher\Timer

Creates a timer that calls the function `$callback` after `$interval` seconds have elapsed. The number of seconds can have a decimal component (e.g., `1.2` to execute the callback in 1.2 seconds).

### Parameters
`float $interval`
:   Number of seconds before the callback is invoked.

`callable(Icicle\Loop\Watcher\Timer $timer): void $callback`
:   Function to invoke when the timer expires.

### Return value
`Icicle\Loop\Events\Timer`
:   A new `Icicle\Loop\Events\Timer` event object.


## periodic()

    Icicle\Loop\periodic(
        float $interval,
        callable(Icicle\Loop\Watcher\Timer $timer): void $callback,
    ): Icicle\Loop\Watcher\Timer

Creates a timer that calls the function `$callback` every `$interval` seconds until stopped. The number of seconds can have a decimal component (e.g., `1.2` to execute the callback in 1.2 seconds).

### Parameters
`float $interval`
:   Number of seconds between invocations of the callback.

`callable(Icicle\Loop\Watcher\Timer $timer): void $callback`
:   Function to invoke when the timer expires.

### Return value
`Icicle\Loop\Events\Timer`
:   A new `Icicle\Loop\Events\Timer` event object.


## immediate()

    Icicle\Loop\immediate(
        callable(Icicle\Loop\Watcher\Immediate $immediate): void $callback,
    ): Icicle\Loop\Watcher\Immediate

Calls the function `$callback` with the given arguments as soon as there are no active events in the loop, only executing one callback per turn of the loop. Functions are guaranteed to be executed in the order queued.

### Parameters
`callable(Icicle\Loop\Watcher\Immediate $immediate): void $callback`
:   Function to invoke when no other active events are available.

### Return value
`Icicle\Loop\Events\Immediate`
:   A new `Icicle\Loop\Events\Immediate` event object.

!!! note
    The name of this function is somewhat misleading, but was chosen because of the similar behavior to the `setImmediate()` function available in some implementations of JavaScript. Think of an immediate as a timer that executes when able rather than after a particular interval.


## signalHandlingEnabled()

    Icicle\Loop\signalHandlingEnabled(): bool

Determines if signals sent to the PHP process can be handled by the event loop.

### Return value
`bool`
:   Returns `true` if signal handling is enabled, `false` if not.

!!! note
    Signal handling requires the `pcntl` extension to be enabled.


## signal()

    Icicle\Loop\signal(
        int $signo,
        callable(int $signo, Icicle\Loop\Watcher\Signal $signal): void $callback
    ): Icicle\Loop\Watcher\Signal

Creates a [`Icicle\Loop\Watcher\Signal`](Watcher.Signal.md) watcher that calls the callback whenever the process receives a signal of the given number.

### Parameters
`int $signo`
:   A POSIX signal number. Use constants such as `SIGTERM`, `SIGCHLD`, etc.

`callable(int $signo, Icicle\Loop\Watcher\Signal $signal): void $callback`
:   Callback invoked each time the signal arrives.

### Return value
`Icicle\Loop\Watcher\Signal`
:   A new `Icicle\Loop\Events\Signal` event object for the given signal number.

!!! warning
    Signal handling requires the `pcntl` extension to be enabled.


## isEmpty()

    Icicle\Loop\isEmpty(): bool

Determines if the are any pending events in the loop.

### Return value
`bool`
:   A boolean indicating if there are any pending events.


## reInit()

    Icicle\Loop\reInit(): void

This function should be called by the child process if the process is forked using [`pcntl_fork()`](http://php.net/pcntl_fork).


## clear()

    Icicle\Loop\clear(): void

Removes all events from the loop, returning the loop to a state like it had just been created.
