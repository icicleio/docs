The Loop component implements an event loop that is used to schedule functions, run timers, handle signals, and poll sockets.

## Loop Functions

Any asynchronous code needs to use the same event loop to be interoperable and non-blocking. The loop component provides the a set of functions in the `Icicle\Loop` namespace that should be used to access the single active event loop. These function act as a container for an instance of `Icicle\Loop\LoopInterface` that actually implements the event loop.

The functions available in `Icicle\Loop` (abbreviated to `Loop` in the function prototypes) are described below.

#### Loop\loop()

```php
Loop\loop(LoopInterface $loop = null): LoopInterface
```

This function accesses the active event loop or, if called with an instance of `Icicle\Loop\LoopInterface`, allows any particular or custom implementation of `Icicle\Loop\LoopInterface` to be used as the event loop. If specifying an event loop, this function should be called before any code that would access the event loop, otherwise a `Icicle\Loop\Exception\InitializedException` will be thrown since the default factory would have already created an event loop.

#### Loop\run()

```php
Loop\run(callable $initialize = null): bool
```

Runs the event loop until there are no events pending in the loop. Returns true if the loop exited because `stop()` was called or false if the loop exited because there were no more pending events.

This function is generally the last line in the script that starts the program, as this function blocks until there are no pending events.

An optional initialization function may be given that is called as soon as the loop is started. This function can be used to create a set of initial events or set up a server.

#### Loop\tick()

```php
Loop\tick(bool $blocking = false): void
```

Executes a single turn of the event loop. Set `$blocking` to `true` to block until at least one pending event becomes active, or set `$blocking` to `false` to return immediately, even if no events are executed.

#### Loop\isRunning()

```php
Loop\isRunning(): bool
```

Determines if the loop is running.

#### Loop\stop()

```php
Loop\stop(): void
```

Stops the loop if it is running.

#### Loop\schedule()

```php
Loop\schedule(callable<(mixed ...$args): void> $callback, mixed ...$args): void
```

Schedules the function `$callback` to be executed later (sometime after leaving the scope calling this method). Functions are guaranteed to be executed in the order queued. This method is useful for ensuring that functions are called asynchronously.

#### Loop\maxScheduleDepth()

```php
Loop\maxScheduleDepth(int $depth): int
```

Sets the maximum number of scheduled functions to execute on each turn of the event loop. Returns the previous max schedule depth.

#### Loop\poll()

```php
Loop\poll(
    resource $socket,
    callable<(resource $socket, bool $expired): void>
): SocketEventInterface
```

Creates an `Icicle\Loop\Events\SocketEventInterface` object for the given stream socket that will listen for data to become available on the socket.

#### Loop\await()

```php
Loop\await(
    resource $socket,
    callable<(resource $socket, bool $expired): void>
): SocketEventInterface
```

Creates an `Icicle\Loop\Events\SocketEventInterface` object for the given stream socket that will listen for the ability to write to the socket.

#### Loop\timer()

```php
Loop\timer(
    float $interval,
    callable<(mixed ...$args): void> $callback,
    mixed ...$args
): TimerInterface
```

Creates a timer that calls the function `$callback` with the given arguments after `$interval` seconds have elapsed. The number of seconds can have a decimal component (e.g., `1.2` to execute the callback in 1.2 seconds). Returns an `Icicle\Loop\Events\TimerInterface` object.

#### Loop\periodic()

```php
Loop\periodic(
    float $interval,
    callable<(mixed ...$args): void> $callback,
    mixed ...$args
): TimerInterface
```

Creates a timer that calls the function `$callback` with the given arguments every `$interval` seconds until cancelled. The number of seconds can have a decimal component (e.g., `1.2` to execute the callback in 1.2 seconds). Returns an `Icicle\Loop\Events\TimerInterface` object.

#### Loop\immediate()

```php
Loop\immediate(
    callable<(mixed ...$args): void> $callback,
    mixed ...$args
): ImmediateInterface
```

Calls the function `$callback` with the given arguments as soon as there are no active events in the loop, only executing one callback per turn of the loop. Returns an `Icicle\Loop\Events\ImmediateInterface` object. Functions are guaranteed to be executed in the order queued.

The name of this function is somewhat misleading, but was chosen because of the similar behavior to the `setImmediate()` function available in some implementations of JavaScript. Think of an immediate as a timer that executes when able rather than after a particular interval.

#### Loop\signalHandlingEnabled()

```php
Loop\signalHandlingEnabled(): bool
```

Determines if signals sent to the PHP process can be handled by the event loop. Returns `true` if signal handling is enabled, `false` if not. Signal handling requires the `pcntl` extension to be installed.

#### Loop\signal()

```php
Loop\signal(int $signo, callable<(int $signo): void>): SignalInterface
```

Creates a process signal listener object implementing `Icicle\Loop\Events\SignalInterface` that calls the callback whenever the process receives a signal of the given number. Use constants such as `SIGTERM`, `SIGCHLD`, etc. for the `$signo` argument.

#### Loop\isEmpty()

```php
Loop\isEmpty(): bool
```

Determines if the are any pending events in the loop.

#### Loop\reInit()

```php
Loop\reInit(): void
```

This function should be called by the child process if the process is forked using `pcntl_fork()`.

#### Loop\clear()

```php
Loop\clear(): void
```

Removes all events from the loop, returning the loop a state like it had just been created.

## Loop Implementations

There are currently three loop classes, each implementing `Icicle\Loop\LoopInterface`. Any custom implementation written must also implement this interface. Custom loop implementations can be used as the active event loop using the `Icicle\Loop\loop()` function ([see function prototype above](#loop)).

- `Icicle\Loop\SelectLoop`: Works with any installation of PHP since it relies only on core functions. Uses `stream_select()` or `time_nanosleep()` depending on the events pending in the loop.
- `Icicle\Loop\EventLoop`: Requires the `event` pecl extension. Preferred implementation for best performance.
- `Icicle\Loop\LibeventLoop`: Requires the `libevent` pecl extension. Also provides better performance than the `SelectLoop` implementation.

While each implementation is different, there should be no difference in the behavior of a program based on the loop implementation used. Note that there may be some differences in the exact timing of the execution of certain events or the order in which different types of events are executed (particularly the ordering of timers and signals). However, programs should not be reliant on the exact timing of callback function execution and therefore should not be affected by these differences. Regardless of implementation, callbacks scheduled with `schedule()` and `immediate()` are always executed in the order queued.

## Throwing Exceptions

Functions scheduled using `Loop\schedule()` or callback functions used for timers, immediates, and socket events should not throw exceptions. If one of these functions throws an exception, it will be thrown from the `Loop\run()` function. These are referred to as *uncatchable exceptions* since there is no way to catch the thrown exception within the event loop. If an exception can be thrown from code within a callback, that code should be surrounded by a try/catch block and the exception handled within the callback.

## Events

When an event is scheduled in the event loop through the methods `poll()`, `await()`, `timer()`, `periodic()`, and `immediate()`, an object implementing `Icicle\Loop\Events\EventInterface` is returned. These objects provide methods for listening, cancelling, or determining if the event is pending.

## SocketEventInterface

A socket event is returned from a poll or await call to the event loop. A poll becomes active when a socket has data available to read, has closed (EOF), or if the timeout provided to `listen()` has expired. An await becomes active when a socket has space in the buffer available to write or if the timeout provided to `listen()` has expired. The callback function associated with the event should have the prototype `callable<void (resource $socket, bool $expired)>`. This function is called with `$expired` set to `false` if there is data available to read on `$socket`, or with `$expired` set to `true` if waiting for data timed out.

Note that you may poll and await a stream socket simultaneously, but multiple socket events cannot be made for the same task (i.e., two polling events or two awaiting events).

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

#### listen()

```php
SocketEventInterface::listen(float $timeout): void
```

Listens for data to become available or the ability to write to the socket. If `$timeout` is not `null`, the poll callback will be called after `$timeout` seconds with `$expired` set to `true`.

#### cancel()

```php
SocketEventInterface::cancel(): void
```

Stops listening for data to become available or ability to write.

#### isPending()

```php
SocketEventInterface::isPending(): bool
```

Determines if the event is listening for data.

#### free()

```php
SocketEventInterface::free(): void
```

Frees the resources allocated to the poll from the event loop. This function should always be called when the event is no longer needed. Once an event has been freed, it cannot be used again and another must be recreated for the same socket resource.

#### isFreed()

```php
SocketEventInterface::isFreed(): bool
```

Determines if the event has been freed from the event loop.

## TimerInterface

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

```php
TimerInterface::start(): void
```

Restarts the timer if it was previously stopped. Note that timers are automatically started when created.

#### stop()

```php
TimerInterface::stop(): void
```

Stops the timer.

#### isPending()

```php
TimerInterface::isPending(): bool
```

Determines if the timer is pending and will be executed in the future.

#### getInterval()

```php
TimerInterface::getInterval(): float
```

Returns the number of seconds set for the timer interval.

#### isPeriodic()

```php
TimerInterface::isPeriodic(): bool
```

Determines if the timer is periodic.

#### unreference()

```php
TimerInterface::unreference(): void
```

Removes the reference to the timer from the event loop. That is, if this timer is the only pending event in the loop, the loop will exit (return from `Icicle\Loop\LoopInterface->run()`).

#### reference()

```php
TimerInterface::reference(): void
```

Adds a reference to the timer in the event loop. If this timer is still pending, the loop will not exit (return from `Icicle\Loop\LoopInterface->run()`). Note when a timer is created, it is referenced by default. This method only need be called if `unreference()` was previously called on the timer.

## ImmediateInterface

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

#### execute()

```php
ImmediateInterface::execute(): void
```

Executes the immediate again if it has already been executed. If the immediate is still pending, this is a no-op.

#### isPending()

```php
ImmediateInterface::isPending(): bool
```

Determines if the immediate is pending and will be executed in the future.

## SignalInterface

A process signal listener is triggered when the PHP process receives a signal matching the signal identifier given when the listener was created. Signal handling requires the `pcntl` extension to be installed.

Signal listeners implement `Icicle\Loop\Events\SignalInterface` and should be created by calling `Icicle\Loop\signal()` as shown below:

```php
use Icicle\Loop;
$signal = Loop\signal(SIGQUIT, function ($signo) {
    // Function executed when a SIGQUIT signal is received.
});
```

See the [Loop function documentation](#signal) above for more information on `Icicle\Loop\signal()`.

#### enable()

```php
SignalInterface::enable(): void
```

Enables the signal listener if it was previously disabled.

#### disable()

```php
SignalInterface::disable(): void
```

Disables the signal listener. It will not be called if a signal arrives at the process until re-enabled.

#### isEnabled()

```php
SignalInterface::isEnabled(): bool
```

Determines if the signal listener is enabled (listening for signals).

#### getSignal()

```php
SignalInterface::getSignal(): int
```

Returns the signal number that will trigger the callback when received. Corresponds to the signal constants such as SIGQUIT, SIGCHLD, etc.
