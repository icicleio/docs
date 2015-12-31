The Loop component implements an event loop that is used to schedule functions, run timers, handle signals, and poll sockets.


## Loop Functions

Any asynchronous code needs to use the same event loop to be interoperable and non-blocking. The loop component provides the a set of functions in the `\Icicle\Loop` namespace that should be used to access the single active event loop. These function act as a container for an instance of `\Icicle\Loop\Loop` that actually implements the event loop.

The functions available in `\Icicle\Loop` (abbreviated to `Loop` in the function prototypes) are described below.

### loop()

    \Icicle\Loop\loop(
        \Icicle\Loop\Loop $loop = null
    ): \Icicle\Loop\Loop

This function accesses the active event loop or, if called with an instance of `\Icicle\Loop\Loop`, allows any particular or custom implementation of `\Icicle\Loop\Loop` to be used as the event loop. If specifying an event loop, this function should be called before any code that would access the event loop, otherwise a `\Icicle\Loop\Exception\InitializedException` will be thrown since the default factory would have already created an event loop.

#### Parameters
`\Icicle\Loop\Loop $loop`
:   An event loop instance to use as the global event loop. To use the default created loop, set as `null`.

#### Return value
`\Icicle\Loop\Loop`
:   The global event loop instance. If an instance was given for `$loop`, the returned loop will be the same instance as `$loop`.


### create()

    \Icicle\Loop\create(bool $enableSignals = true): \Icicle\Loop\Loop

Creates a new event loop instance using the best implementation available. Available implementations depends on the configured and enabled extensions. If no extensions are available, a `\Icicle\Loop\SelectLoop` instance will be created. See [loop implementations](#loop-implementations) for details.

#### Parameters
`bool $enableSignals`
:   By default, process signal handling will be enabled in the created event loop. To disable signal handling, set this to `false`.

#### Return value
`\Icicle\Loop\Loop`
:   The new event loop instance.


### run()

    \Icicle\Loop\run(callable $initialize = null): bool

Runs the event loop until there are no events pending in the loop.

This function is generally the last line in the script that starts the program, as this function blocks until there are no pending events.

#### Parameters
`callable(): mixed|null $initialize`
:   An optional initialization function that is called as soon as the loop is started. This function can be used to create a set of initial events or set up a server.

#### Return value
`bool`
:   Returns `true` if the loop exited because `stop()` was called or `false` if the loop exited because there were no more pending events.


### with()

    \Icicle\Loop\with(
        callable(): mixed $worker,
        \Icicle\Loop\Loop|null $loop = null
    ): bool

Runs the tasks set up in the given `$worker` function in a separate, specified event loop from the default event loop. If the default event loop is currently running, it will be blocked while the separate event loop runs.

#### Parameters
`callable(): mixed $worker`
:   The function that should be invoked using a separate event loop. Calling [`loop()`](#loop) inside this function will return the overriden event loop.

`\Icicle\Loop\Loop|null $loop = null`
: The loop to run the tasks created by `$worker` in. If no loop is specified, a new loop instance will be automatically created and used.

#### Return value
`bool`
:   Returns `true` if the loop exited because `stop()` was called or `false` if the loop exited because there were no more pending events.


### queue()

    \Icicle\Loop\queue(
        callable(mixed ...$args): mixed $callback,
        mixed ...$args
    ): void

Queues a function to be executed later. The function may be executed as soon as immediately after the calling scope exits. Functions are guaranteed to be executed in the order queued. This function is useful for ensuring that functions are called asynchronously.

!!! note
    This function is used internally by Icicle to ensure tasks are executed asynchronously and do not blow up the call stack, but is generally unnecessary to use this function elsewhere.

#### Parameters
`callable(mixed ...$args): mixed$callback`
:   The callback function to queue.

`mixed ...$args`
:   Parameters to pass to the callback function.

### tick()

    \Icicle\Loop\tick(bool $blocking = false): void

Executes a single turn of the event loop.

#### Parameters
`bool $blocking = false`
:   Set to `true` to block until at least one pending event becomes active, or set to `false` to return immediately, even if no events are executed.


### isRunning()

    \Icicle\Loop\isRunning(): bool

Determines if the loop is currently running.

#### Return value
`bool`
:   A boolean indicating if the loop is running.


### stop()

    \Icicle\Loop\stop(): void

Stops the loop if it is running.


### maxQueueDepth()

    \Icicle\Loop\maxQueueDepth(int $depth): int

Sets the maximum number of callbacks set with [`queue()`](#loopqueue) that will be executed per tick.

#### Parameters
`int $depth`
:   Maximum number of functions to execute each tick. Use 0 for unlimited.

#### Return value
`int`
:   The previous max schedule depth.


### poll()

    \Icicle\Loop\poll(
        resource $resource,
        callable(
            resource $resource,
            bool $expired,
            \Icicle\Loop\Watcher\Io
        ): void $callback,
        bool $persistent = false
    ): \Icicle\Loop\Watcher\Io

Creates an `\Icicle\Loop\Watcher\Io` object for the given stream socket that will listen for data to become available on the stream.

#### Parameters
`resource $resource`
:   A stream socket resource to poll.

`callable(resource $resource, bool $expired, \Icicle\Loop\Watcher\Io $io): void $callback`
:   Callback to be invoked when data is available on the stream.

`bool $persistent = false`
:   If `true`, calling `listen()` on the returned `Io` watcher will continue listening until `cancel()` is called on the watcher.

#### Return value
`\Icicle\Loop\Watcher\Io`
:   Watcher object for polling the socket for data.


### await()

    \Icicle\Loop\await(
        resource $resource,
        callable(
            resource $resource,
            bool $expired,
            \Icicle\Loop\Watcher\Io
        ): void $callback,
        bool $persistent = false
    ): \Icicle\Loop\Watcher\Io

Creates an `\Icicle\Loop\Watcher\Io` object for the given stream socket that will listen for the ability to write to the stream.

#### Parameters
`resource $resource`
:   A stream socket resource to await.

`callable(resource $resource, bool $expired, \Icicle\Loop\Watcher\Io $io): void $callback`
:   Callback to be invoked when the stream is available to write.

`bool $persistent = false`
:   If `true`, calling `listen()` on the returned `Io` watcher will continue listening until `cancel()` is called on the watcher.

#### Return value
`\Icicle\Loop\Watcher\Io`
:   Watcher object for awaiting space to write.


### timer()

    \Icicle\Loop\timer(
        float $interval,
        callable(\Icicle\Loop\Watcher\Timer $timer): void $callback,
    ): \Icicle\Loop\Watcher\Timer

Creates a timer that calls the function `$callback` after `$interval` seconds have elapsed. The number of seconds can have a decimal component (e.g., `1.2` to execute the callback in 1.2 seconds).

#### Parameters
`float $interval`
:   Number of seconds before the callback is invoked.

`callable(\Icicle\Loop\Watcher\Timer $timer): void $callback`
:   Function to invoke when the timer expires.

#### Return value
`\Icicle\Loop\Events\Timer`
:   A new `\Icicle\Loop\Events\Timer` event object.


### periodic()

    \Icicle\Loop\periodic(
        float $interval,
        callable(\Icicle\Loop\Watcher\Timer $timer): void $callback,
    ): \Icicle\Loop\Watcher\Timer

Creates a timer that calls the function `$callback` every `$interval` seconds until stopped. The number of seconds can have a decimal component (e.g., `1.2` to execute the callback in 1.2 seconds).

#### Parameters
`float $interval`
:   Number of seconds between invocations of the callback.

`callable(\Icicle\Loop\Watcher\Timer $timer): void $callback`
:   Function to invoke when the timer expires.

#### Return value
`\Icicle\Loop\Events\Timer`
:   A new `\Icicle\Loop\Events\Timer` event object.


### immediate()

    \Icicle\Loop\immediate(
        callable(\Icicle\Loop\Watcher\Immediate $immediate): void $callback,
    ): \Icicle\Loop\Watcher\Immediate

Calls the function `$callback` with the given arguments as soon as there are no active events in the loop, only executing one callback per turn of the loop. Functions are guaranteed to be executed in the order queued.

#### Parameters
`callable(\Icicle\Loop\Watcher\Immediate $immediate): void $callback`
:   Function to invoke when no other active events are available.

#### Return value
`\Icicle\Loop\Events\Immediate`
:   A new `\Icicle\Loop\Events\Immediate` event object.

!!! note
    The name of this function is somewhat misleading, but was chosen because of the similar behavior to the `setImmediate()` function available in some implementations of JavaScript. Think of an immediate as a timer that executes when able rather than after a particular interval.


### signalHandlingEnabled()

    \Icicle\Loop\signalHandlingEnabled(): bool

Determines if signals sent to the PHP process can be handled by the event loop.

#### Return value
`bool`
:   Returns `true` if signal handling is enabled, `false` if not.

!!! note
    Signal handling requires the `pcntl` extension to be enabled.


### signal()

    \Icicle\Loop\signal(
        int $signo,
        callable(int $signo, \Icicle\Loop\Watcher\Signal $signal): void $callback
    ): \Icicle\Loop\Watcher\Signal

Creates a process signal listener object implementing `\Icicle\Loop\Events\Signal` that calls the callback whenever the process receives a signal of the given number.

#### Parameters
`int $signo`
:   A POSIX signal number. Use constants such as `SIGTERM`, `SIGCHLD`, etc.

`callable(int $signo, \Icicle\Loop\Watcher\Signal $signal): void $callback`
:   Callback invoked each time the signal arrives.

#### Return value
`\Icicle\Loop\Watcher\Signal`
:   A new `\Icicle\Loop\Events\Signal` event object for the given signal number.

!!! warning
    Signal handling requires the `pcntl` extension to be enabled.


### isEmpty()

    \Icicle\Loop\isEmpty(): bool

Determines if the are any pending events in the loop.

#### Return value
`bool`
:   A boolean indicating if there are any pending events.


### reInit()

    \Icicle\Loop\reInit(): void

This function should be called by the child process if the process is forked using `pcntl_fork()`.


### clear()

    \Icicle\Loop\clear(): void

Removes all events from the loop, returning the loop to a state like it had just been created.


## Watcher\Io

An IO watcher is returned from a poll or await call to the event loop. A poll becomes active when a stream has data available to read, has closed (EOF), or if the timeout provided to `listen()` has expired. An await becomes active when a stream has space in the buffer available to write or if the timeout provided to `listen()` has expired. The callback function associated with the event should have the prototype `callable(resource $resource, bool $expired, \Icicle\Loop\Watcher\Io $io): void`. This function is called with `$expired` set to `false` if there is data available to read on `$resource`, or with `$expired` set to `true` if waiting for data timed out.

!!! note
    You may poll and await a stream socket simultaneously, but multiple IO events cannot be made for the same type of task (i.e., two polling events or two awaiting events).

IO objects should be created by calling `\Icicle\Loop\poll()` to poll for data or `\Icicle\Loop\await()` to wait for the ability to write.

```php
use Icicle\Loop;

// $socket is a stream socket resource.

$poll = Loop\poll($socket, function ($socket, $expired) {
    // Read data from socket or handle timeout.
});

$poll = Loop\await($socket, function ($socket, $expired) {
    // Write data to socket or handle timeout.
});
```

See the [Loop function documentation](#poll) above for more information on `\Icicle\Loop\poll()` and `\Icicle\Loop\await()`.

### listen()

    Io::listen(float $timeout = 0): void

Listens for data to become available or the ability to write to the socket. If `$timeout` is not `0`, the poll callback will be called after `$timeout` seconds with `$expired` set to `true`.

#### Parameters
`float $timeout = 0`
:   Number of seconds until the callback is invoked with `$expired` set to `true` if no data is received or the socket does not become writable. Use `0` for no timeout.


### cancel()

    Io::cancel(): void

Stops listening for data to become available or ability to write.


### isPending()

    Io::isPending(): bool

Determines if the event is listening for data.

#### Return value
`bool`
:   A boolean indicating if the event is pending.


### free()

    Io::free(): void

Frees the resources allocated to the poll from the event loop. This function should always be called when the event is no longer needed. Once an event has been freed, it cannot be used again and another must be recreated for the same socket resource.


### isFreed()

    Io::isFreed(): bool

Determines if the watcher has been freed from the event loop.

#### Return value
`bool`
:   A boolean indicating if the event has been freed.


### isPersistent()

    Io::isPersistent(): bool

Determines if the watcher is persistent (calling `listen()` will continue polling until `cancel()` is called).

#### Return value
`bool`
:   A boolean indicating if the event has been freed.


### unreference()

    Io::unreference(): void

Removes the reference to the watcher from the event loop. That is, if this watcher is the only pending watcher in the loop, the loop will exit (return from `\Icicle\Loop\Loop::run()`).


### reference()

    Io::reference(): void

Adds a reference to the watcher in the event loop. If this watcher is still pending, the loop will not exit (return from `\Icicle\Loop\Loop::run()`). Note when an IO watcher is created, it is referenced by default. This method only need be called if `unreference()` was previously called on the IO watcher.


## Watcher\Timer

Timers are used to execute a callback function after an amount of time has elapsed. Timers may be one-time, executing the callback only once, or periodic, executing the callback many times separated by an interval. Timers may be stopped and restarted.

Timers should be created by calling `\Icicle\Loop\timer()` for one-time timers and `\Icicle\Loop\periodic()` for periodic timers. An example is shown below:

```php
use Icicle\Loop;
$timer = Loop\timer(1.3, function () {
    // Function executed after 1.3 seconds have elapsed.
});
```

See the [Loop function documentation](#timer) above for more information on `\Icicle\Loop\timer()` and `\Icicle\Loop\periodic()`.

### start()

    Timer::start(): void

Restarts the timer if it was previously stopped. Note that timers are automatically started when created.


### stop()

    Timer::stop(): void

Stops the timer.


### isPending()

    Timer::isPending(): bool

Determines if the timer is pending and will be executed in the future.

#### Return value
`bool`
:   A boolean indicating if the event is pending.


### getInterval()

    Timer::getInterval(): float

Gets the number of seconds set for the timer interval.

#### Return value
`float`
:   The number of seconds for the timer interval.


### isPeriodic()

    Timer::isPeriodic(): bool

Determines if the timer is periodic.

#### Return value
`bool`
:   A boolean indicating if the timer is periodic.


### unreference()

    Timer::unreference(): void

Removes the reference to the watcher from the event loop. That is, if this watcher is the only pending watcher in the loop, the loop will exit (return from `\Icicle\Loop\Loop::run()`).


### reference()

    Timer::reference(): void

Adds a reference to the watcher in the event loop. If this watcher is still pending, the loop will not exit (return from `\Icicle\Loop\Loop::run()`). Note when a timer is created, it is referenced by default. This method only need be called if `unreference()` was previously called on the timer.


## Watcher\Immediate

An immediate schedules a callback to be called when there are no active events in the loop, only executing one immediate per turn of the event loop.

The name immediate is somewhat misleading, but was chosen because of the similar behavior to the `setImmediate()` function available in some implementations of JavaScript. Think of an immediate as a timer that executes when able rather than after a particular interval.

Immediates should be created by calling `\Icicle\Loop\immediate()` as shown below:

```php
use Icicle\Loop;
$immediate = Loop\immediate(function () {
    // Function executed when no events are active in the event loop.
});
```

See the [Loop function documentation](#immediate) above for more information on `\Icicle\Loop\immediate()`.

### execute()

    Immediate::execute(): void

Executes the immediate again if it has already been executed. If the immediate is still pending, this is a no-op.


### isPending()

    Immediate::isPending(): bool

Determines if the immediate is pending and will be executed in the future.

#### Return value
`bool`
:   A boolean indicating if the event is pending.


### unreference()

    Immediate::unreference(): void

Removes the reference to the watcher from the event loop. That is, if this watcher is the only pending watcher in the loop, the loop will exit (return from `\Icicle\Loop\Loop::run()`).


### reference()

    Immediate::reference(): void

Adds a reference to the watcher in the event loop. If this watcher is still pending, the loop will not exit (return from `\Icicle\Loop\Loop::run()`). Note when a immediate is created, it is referenced by default. This method only need be called if `unreference()` was previously called on the immediate.


## Watcher\Signal

A process signal watcher is triggered when the PHP process receives a signal matching the signal identifier given when the watcher was created. Signal handling requires the `pcntl` extension to be installed.

Signal listeners should be created by calling `\Icicle\Loop\signal()` as shown below:

```php
use Icicle\Loop;
$signal = Loop\signal(SIGQUIT, function ($signo) {
    // Function executed when a SIGQUIT signal is received.
});
```

See the [Loop function documentation](#signal) above for more information on `\Icicle\Loop\signal()`.

### enable()

    Signal::enable(): void

Enables the signal listener if it was previously disabled.


### disable()

    Signal::disable(): void

Disables the signal listener. It will not be called if a signal arrives at the process until re-enabled.


### isEnabled()

    Signal::isEnabled(): bool

Determines if the signal listener is enabled (listening for signals).

#### Return value
`bool`
:   A boolean indicating if the event is enabled.


### getSignal()

    Signal::getSignal(): int

Gets the signal number that will trigger the callback when received.

#### Return value
`int`
:   A process signal number. Corresponds to the signal constants such as `SIGQUIT`, `SIGCHLD`, etc.


### unreference()

    Signal::unreference(): void

Removes the reference to the watcher from the event loop. That is, if this watcher is the only pending watcher in the loop, the loop will exit (return from `\Icicle\Loop\Loop::run()`). Note when a signal watcher is created, it is unreferenced by default. This method only need be called if `reference()` was previously called on the signal watcher.


### reference()

    Signal::reference(): void

Adds a reference to the watcher in the event loop. If this watcher is still pending, the loop will not exit (return from `\Icicle\Loop\Loop::run()`).