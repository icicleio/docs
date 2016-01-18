A process signal watcher is triggered when the PHP process receives a signal matching the signal identifier given when the watcher was created. Signal handling requires the `pcntl` extension to be installed.

Signal listeners should be created by calling `Icicle\Loop\signal()` as shown below:

```php
use Icicle\Loop;
$signal = Loop\signal(SIGQUIT, function ($signo) {
    // Function executed when a SIGQUIT signal is received.
});
```

See the [Loop function documentation](index.md#signal) for more information on `Icicle\Loop\signal()`.


## enable()

    Signal::enable(): void

Enables the signal listener if it was previously disabled.


## disable()

    Signal::disable(): void

Disables the signal listener. It will not be called if a signal arrives at the process until re-enabled.


## isEnabled()

    Signal::isEnabled(): bool

Determines if the signal listener is enabled (listening for signals).

#### Return value
`bool`
:   A boolean indicating if the event is enabled.


## getSignal()

    Signal::getSignal(): int

Gets the signal number that will trigger the callback when received.

#### Return value
`int`
:   A process signal number. Corresponds to the signal constants such as `SIGQUIT`, `SIGCHLD`, etc.


## unreference()

    Signal::unreference(): void

Removes the reference to the watcher from the event loop. That is, if this watcher is the only pending watcher in the loop, the loop will exit (return from `Icicle\Loop\Loop::run()`). Note when a signal watcher is created, it is unreferenced by default. This method only need be called if `reference()` was previously called on the signal watcher.


## reference()

    Signal::reference(): void

Adds a reference to the watcher in the event loop. If this watcher is still pending, the loop will not exit (return from `Icicle\Loop\Loop::run()`).
