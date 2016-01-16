An immediate schedules a callback to be called when there are no active events in the loop, only executing one immediate per turn of the event loop.

The name immediate is somewhat misleading, but was chosen because of the similar behavior to the `setImmediate()` function available in some implementations of JavaScript. Think of an immediate as a timer that executes when able rather than after a particular interval.

Immediates should be created by calling `Icicle\Loop\immediate()` as shown below:

```php
use Icicle\Loop;
$immediate = Loop\immediate(function () {
    // Function executed when no events are active in the event loop.
});
```

See the [Loop function documentation](#immediate) above for more information on `Icicle\Loop\immediate()`.

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

Removes the reference to the watcher from the event loop. That is, if this watcher is the only pending watcher in the loop, the loop will exit (return from `Icicle\Loop\Loop::run()`).


### reference()

    Immediate::reference(): void

Adds a reference to the watcher in the event loop. If this watcher is still pending, the loop will not exit (return from `Icicle\Loop\Loop::run()`). Note when a immediate is created, it is referenced by default. This method only need be called if `unreference()` was previously called on the immediate.
