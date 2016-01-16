Timers are used to execute a callback function after an amount of time has elapsed. Timers may be one-time, executing the callback only once, or periodic, executing the callback many times separated by an interval. Timers may be stopped and restarted.

Timers should be created by calling `Icicle\Loop\timer()` for one-time timers and `Icicle\Loop\periodic()` for periodic timers. An example is shown below:

```php
use Icicle\Loop;
$timer = Loop\timer(1.3, function () {
    // Function executed after 1.3 seconds have elapsed.
});
```

See the [Loop function documentation](#timer) above for more information on `Icicle\Loop\timer()` and `Icicle\Loop\periodic()`.

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

Removes the reference to the watcher from the event loop. That is, if this watcher is the only pending watcher in the loop, the loop will exit (return from `Icicle\Loop\Loop::run()`).


### reference()

    Timer::reference(): void

Adds a reference to the watcher in the event loop. If this watcher is still pending, the loop will not exit (return from `Icicle\Loop\Loop::run()`). Note when a timer is created, it is referenced by default. This method only need be called if `unreference()` was previously called on the timer.
