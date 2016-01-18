An IO watcher is returned from a poll or await call to the event loop. A poll becomes active when a stream has data available to read, has closed (EOF), or if the timeout provided to `listen()` has expired. An await becomes active when a stream has space in the buffer available to write or if the timeout provided to `listen()` has expired. The callback function associated with the event should have the prototype `callable(resource $resource, bool $expired, Icicle\Loop\Watcher\Io $io): void`. This function is called with `$expired` set to `false` if there is data available to read on `$resource`, or with `$expired` set to `true` if waiting for data timed out.

!!! note
    You may poll and await a stream socket simultaneously, but multiple IO events cannot be made for the same type of task (i.e., two polling events or two awaiting events).

IO objects should be created by calling `Icicle\Loop\poll()` to poll for data or `Icicle\Loop\await()` to wait for the ability to write.

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

See the [Loop function documentation](index.md#poll) for more information on `Icicle\Loop\poll()` and `Icicle\Loop\await()`.


## listen()

    Io::listen(float $timeout = 0): void

Listens for data to become available or the ability to write to the socket. If `$timeout` is not `0`, the poll callback will be called after `$timeout` seconds with `$expired` set to `true`.

### Parameters
`float $timeout = 0`
:   Number of seconds until the callback is invoked with `$expired` set to `true` if no data is received or the socket does not become writable. Use `0` for no timeout.


## cancel()

    Io::cancel(): void

Stops listening for data to become available or ability to write.


## isPending()

    Io::isPending(): bool

Determines if the event is listening for data.

### Return value
`bool`
:   A boolean indicating if the event is pending.


## free()

    Io::free(): void

Frees the resources allocated to the poll from the event loop. This function should always be called when the event is no longer needed. Once an event has been freed, it cannot be used again and another must be recreated for the same socket resource.


## isFreed()

    Io::isFreed(): bool

Determines if the watcher has been freed from the event loop.

### Return value
`bool`
:   A boolean indicating if the event has been freed.


## isPersistent()

    Io::isPersistent(): bool

Determines if the watcher is persistent (calling `listen()` will continue polling until `cancel()` is called).

### Return value
`bool`
:   A boolean indicating if the event has been freed.


## unreference()

    Io::unreference(): void

Removes the reference to the watcher from the event loop. That is, if this watcher is the only pending watcher in the loop, the loop will exit (return from `Icicle\Loop\Loop::run()`).


## reference()

    Io::reference(): void

Adds a reference to the watcher in the event loop. If this watcher is still pending, the loop will not exit (return from `Icicle\Loop\Loop::run()`). Note when an IO watcher is created, it is referenced by default. This method only need be called if `unreference()` was previously called on the IO watcher.
