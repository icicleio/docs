The Loop component implements an event loop that is used to schedule functions, run timers, handle signals, and poll sockets.



## Loop Functions

Any asynchronous code needs to use the same event loop to be interoperable and non-blocking. The loop component provides the a set of functions in the `Icicle\Loop` namespace that should be used to access the single active event loop. These function act as a container for an instance of `Icicle\Loop\LoopInterface` that actually implements the event loop.

The functions available in `Icicle\Loop` (abbreviated to `Loop` in the function prototypes) are described below.

### Loop\loop()

    Loop\loop(LoopInterface $loop = null): LoopInterface

This function accesses the active event loop or, if called with an instance of `Icicle\Loop\LoopInterface`, allows any particular or custom implementation of `Icicle\Loop\LoopInterface` to be used as the event loop. If specifying an event loop, this function should be called before any code that would access the event loop, otherwise a `Icicle\Loop\Exception\InitializedException` will be thrown since the default factory would have already created an event loop.

#### Parameters
`$loop`
:   An event loop instance to use as the global event loop. To use the default created loop, set as `null`.

#### Return value
The global event loop instance. If an instance was given for `$loop`, the returned loop will be the same instance as `$loop`.


### Loop\create()

    Loop\create(bool $enableSignals = true): LoopInterface

Creates a new event loop instance using the best implementation available. Available implementations depends on the configured and enabled extensions. If no extensions are available, a `SelectLoop` will be created. See [loop implementations](#loop-implementations) for details.

#### Parameters
`$enableSignals`
:   By default, process signal handling will be enabled in the created event loop. To disable signal handling, set this to `false`.

#### Return value
The new event loop instance.


### Loop\run()

    Loop\run(callable $initialize = null): bool

Runs the event loop until there are no events pending in the loop.

This function is generally the last line in the script that starts the program, as this function blocks until there are no pending events.

#### Parameters
`$initialize`
:   An optional initialization function that is called as soon as the loop is started. This function can be used to create a set of initial events or set up a server.

#### Return value
Returns `true` if the loop exited because `stop()` was called or `false` if the loop exited because there were no more pending events.


### Loop\with()

    Loop\with(callable $worker, LoopInterface $loop = null): bool

Runs the tasks set up in the given `$worker` function in a separate, specified event loop from the default event loop. If the default event loop is currently running, it will be blocked while the separate event loop runs.

#### Parameters
`$worker`
:   The function that should be invoked using a separate event loop. Calling [`Loop\loop()`](#looploop) inside this function will return the overriden event loop.

`$loop`
: The loop to run the tasks created by `$worker` in. If no loop is specified, a new loop instance will be created and used.

#### Return value
Returns `true` if the loop exited because `stop()` was called or `false` if the loop exited because there were no more pending events.


### Loop\queue()

    Loop\queue(callable $callback, ...$args): void

Queues a function to be executed later. The function may be executed as soon as immediately after the calling scope exits. Functions are guaranteed to be executed in the order queued. This function is useful for ensuring that functions are called asynchronously.

#### Parameters
`$callback`
:   The callback function to queue.


### Loop\tick()

    Loop\tick(bool $blocking = false): void

Executes a single turn of the event loop.

#### Parameters
`$blocking`
:   Set to `true` to block until at least one pending event becomes active, or set to `false` to return immediately, even if no events are executed.


### Loop\isRunning()

    Loop\isRunning(): bool

Determines if the loop is currently running.

#### Return value
A boolean indicating if the loop is running.


### Loop\stop()

    Loop\stop(): void

Stops the loop if it is running.


### Loop\maxQueueDepth()

    Loop\maxQueueDepth(int $depth): int

Sets the maximum number of callbacks set with [`queue()`](#loopqueue) that will be executed per tick.

#### Parameters
`$depth`
:   Maximum number of functions to execute each tick. Use 0 for unlimited.

#### Return value
The previous max schedule depth.


### Loop\poll()

    Loop\poll(
        resource $socket,
        callable<(resource $socket, bool $expired): void> $callback
    ): SocketEventInterface

Creates an `Icicle\Loop\Events\SocketEventInterface` object for the given stream socket that will listen for data to become available on the socket.

#### Parameters
`$socket`
:   A stream socket resource to poll.

`$callback`
:   Callback to be invoked when data is available on the socket.

#### Return value
A new `Icicle\Loop\Events\SocketEventInterface` event object.


### Loop\await()

    Loop\await(
        resource $socket,
        callable<(resource $socket, bool $expired): void> $callback
    ): SocketEventInterface

Creates an `Icicle\Loop\Events\SocketEventInterface` object for the given stream socket that will listen for the ability to write to the socket.

#### Parameters
`$socket`
:   A stream socket resource to await.

`$callback`
:   Callback to be invoked when the socket is available to write.

#### Return value
A new `Icicle\Loop\Events\SocketEventInterface` event object.


### Loop\timer()

    Loop\timer(
        float $interval,
        callable<(mixed ...$args): void> $callback,
        mixed ...$args
    ): TimerInterface

Creates a timer that calls the function `$callback` with the given arguments after `$interval` seconds have elapsed. The number of seconds can have a decimal component (e.g., `1.2` to execute the callback in 1.2 seconds).

#### Parameters
`$interval`
:   Number of seconds before the callback is invoked.

`$callback`
:   Function to invoke when the timer expires.

`...$args`
:   Arguments to pass to the callback function.

#### Return value
A new `Icicle\Loop\Events\TimerInterface` event object.


### Loop\periodic()

    Loop\periodic(
        float $interval,
        callable<(mixed ...$args): void> $callback,
        mixed ...$args
    ): TimerInterface

Creates a timer that calls the function `$callback` with the given arguments every `$interval` seconds until cancelled. The number of seconds can have a decimal component (e.g., `1.2` to execute the callback in 1.2 seconds).

#### Parameters
`$interval`
:   Number of seconds between invocations of the callback.

`$callback`
:   Function to invoke when the timer expires.

`...$args`
:   Arguments to pass to the callback function.

#### Return value
A new `Icicle\Loop\Events\TimerInterface` event object.


### Loop\immediate()

    Loop\immediate(
        callable<(mixed ...$args): void> $callback,
        mixed ...$args
    ): ImmediateInterface

Calls the function `$callback` with the given arguments as soon as there are no active events in the loop, only executing one callback per turn of the loop. Functions are guaranteed to be executed in the order queued.

#### Parameters
`$callback`
:   Function to invoke when no other active events are available.

`...$args`
:   Arguments to pass to the callback function.

#### Return value
A new `Icicle\Loop\Events\ImmediateInterface` event object.

!!! note
    The name of this function is somewhat misleading, but was chosen because of the similar behavior to the `setImmediate()` function available in some implementations of JavaScript. Think of an immediate as a timer that executes when able rather than after a particular interval.


### Loop\signalHandlingEnabled()

    Loop\signalHandlingEnabled(): bool

Determines if signals sent to the PHP process can be handled by the event loop.

#### Return value
Returns `true` if signal handling is enabled, `false` if not.

!!! note
    Signal handling requires the `pcntl` extension to be enabled.


### Loop\signal()

    Loop\signal(
        int $signo,
        callable<(int $signo): void> $callback
    ): SignalInterface

Creates a process signal listener object implementing `Icicle\Loop\Events\SignalInterface` that calls the callback whenever the process receives a signal of the given number.

#### Parameters
`$signo`
:   A POSIX signal number. Use constants such as `SIGTERM`, `SIGCHLD`, etc.

`...$args`
:   Arguments to pass to the callback function.

#### Return value
A new `Icicle\Loop\Events\SignalInterface` event object.

!!! warning
    Signal handling requires the `pcntl` extension to be enabled.


### Loop\isEmpty()

    Loop\isEmpty(): bool

Determines if the are any pending events in the loop.

#### Return value
A boolean indicating if there are any pending events.


### Loop\reInit()

    Loop\reInit(): void

This function should be called by the child process if the process is forked using `pcntl_fork()`.


### Loop\clear()

    Loop\clear(): void

Removes all events from the loop, returning the loop a state like it had just been created.



## Events\SocketEventInterface

A socket event is returned from a poll or await call to the event loop. A poll becomes active when a socket has data available to read, has closed (EOF), or if the timeout provided to `listen()` has expired. An await becomes active when a socket has space in the buffer available to write or if the timeout provided to `listen()` has expired. The callback function associated with the event should have the prototype `callable<void (resource $socket, bool $expired)>`. This function is called with `$expired` set to `false` if there is data available to read on `$socket`, or with `$expired` set to `true` if waiting for data timed out.

!!! note
    You may poll and await a stream socket simultaneously, but multiple socket events cannot be made for the same type of task (i.e., two polling events or two awaiting events).

Socket event objects implement `Icicle\Loop\Events\SocketEventInterface` and should be created by calling `Icicle\Loop\poll()` to poll for data or `Icicle\Loop\await()` to wait for the ability to write.

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

See the [Loop function documentation](#poll) above for more information on `Icicle\Loop\poll()` and `Icicle\Loop\await()`.

### listen()

    SocketEventInterface::listen(float $timeout): void

Listens for data to become available or the ability to write to the socket. If `$timeout` is not `null`, the poll callback will be called after `$timeout` seconds with `$expired` set to `true`.

#### Parameters
`$timeout`
:   Number of seconds until the callback is invoked with `$expired` set to `true` if no data is received or the socket does not become writable. Use `null` for no timeout.


### cancel()

    SocketEventInterface::cancel(): void

Stops listening for data to become available or ability to write.


### isPending()

    SocketEventInterface::isPending(): bool

Determines if the event is listening for data.

#### Return value
A boolean indicating if the event is pending.


### free()

    SocketEventInterface::free(): void

Frees the resources allocated to the poll from the event loop. This function should always be called when the event is no longer needed. Once an event has been freed, it cannot be used again and another must be recreated for the same socket resource.


### isFreed()

    SocketEventInterface::isFreed(): bool

Determines if the event has been freed from the event loop.

#### Return value
A boolean indicating if the event has been freed.



## Events\TimerInterface

Timers are used to execute a callback function after an amount of time has elapsed. Timers may be one-time, executing the callback only once, or periodic, executing the callback many times separated by an interval. Timers may be stopped and restarted.

Timers implement `Icicle\Loop\Events\TimerInterface` and should be created by calling `Icicle\Loop\timer()` for one-time timers and `Icicle\Loop\periodic()` for periodic timers. An example is shown below:

```php
use Icicle\Loop;
$timer = Loop\timer(1.3, function () {
    // Function executed after 1.3 seconds have elapsed.
});
```

See the [Loop function documentation](#timer) above for more information on `Icicle\Loop\timer()` and `Icicle\Loop\periodic()`.

### start()

    TimerInterface::start(): void

Restarts the timer if it was previously stopped. Note that timers are automatically started when created.


### stop()

    TimerInterface::stop(): void

Stops the timer.


### isPending()

    TimerInterface::isPending(): bool

Determines if the timer is pending and will be executed in the future.

#### Return value
A boolean indicating if the event is pending.


### getInterval()

    TimerInterface::getInterval(): float

Gets the number of seconds set for the timer interval.

#### Return value
The number of seconds for the timer interval.


### isPeriodic()

    TimerInterface::isPeriodic(): bool

Determines if the timer is periodic.

#### Return value
A boolean indicating if the timer is periodic.


### unreference()

    TimerInterface::unreference(): void

Removes the reference to the timer from the event loop. That is, if this timer is the only pending event in the loop, the loop will exit (return from `Icicle\Loop\LoopInterface->run()`).


### reference()

    TimerInterface::reference(): void

Adds a reference to the timer in the event loop. If this timer is still pending, the loop will not exit (return from `Icicle\Loop\LoopInterface->run()`). Note when a timer is created, it is referenced by default. This method only need be called if `unreference()` was previously called on the timer.



## Events\ImmediateInterface

An immediate schedules a callback to be called when there are no active events in the loop, only executing one immediate per turn of the event loop.

The name immediate is somewhat misleading, but was chosen because of the similar behavior to the `setImmediate()` function available in some implementations of JavaScript. Think of an immediate as a timer that executes when able rather than after a particular interval.

Immediates implement `Icicle\Loop\Events\ImmediateInterface` and should be created by calling `Icicle\Loop\immediate()` as shown below:

```php
use Icicle\Loop;
$immediate = Loop\immediate(function () {
    // Function executed when no events are active in the event loop.
});
```

See the [Loop function documentation](#immediate) above for more information on `Icicle\Loop\immediate()`.

### execute()

    ImmediateInterface::execute(): void

Executes the immediate again if it has already been executed. If the immediate is still pending, this is a no-op.


### isPending()

    ImmediateInterface::isPending(): bool

Determines if the immediate is pending and will be executed in the future.

#### Return value
A boolean indicating if the event is pending.



## Events\SignalInterface

A process signal listener is triggered when the PHP process receives a signal matching the signal identifier given when the listener was created. Signal handling requires the `pcntl` extension to be installed.

Signal listeners implement `Icicle\Loop\Events\SignalInterface` and should be created by calling `Icicle\Loop\signal()` as shown below:

```php
use Icicle\Loop;
$signal = Loop\signal(SIGQUIT, function ($signo) {
    // Function executed when a SIGQUIT signal is received.
});
```

See the [Loop function documentation](#signal) above for more information on `Icicle\Loop\signal()`.

### enable()

    SignalInterface::enable(): void

Enables the signal listener if it was previously disabled.


### disable()

    SignalInterface::disable(): void

Disables the signal listener. It will not be called if a signal arrives at the process until re-enabled.


### isEnabled()

    SignalInterface::isEnabled(): bool

Determines if the signal listener is enabled (listening for signals).

#### Return value
A boolean indicating if the event is enabled.


### getSignal()

    SignalInterface::getSignal(): int

Gets the signal number that will trigger the callback when received.

#### Return value
A process signal number. Corresponds to the signal constants such as `SIGQUIT`, `SIGCHLD`, etc.
