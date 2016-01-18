## from()

    Observable\from(
        array|\Traversable|\Icicle\Observable\Observable $traversable
    ): Observable

Creates a new observable from some other object or data type.

### Parameters
`$traversable`
:   A traversable data collection to create an observable from.

### Return value
A new observable that emits values from the given object.

### Throws
`Icicle\Exception\InvalidArgumentError`
:   Thrown if an observable could not be created for the given data type.


## merge()

    Observable\merge(array $observables): Observable

Creates a new observable that emits all values from a given array of observables.

### Return value
The new observable.


## observe()

    Observable\observe(
        callable(mixed ...$args) $emitter,
        callable(callable $callback, \Exception $exception) $onDisposed = null,
        int $index = 0,
        mixed ...$args
    ): Observable

Converts a function accepting a callback ($emitter) that invokes the callback when an event is emitted into an observable that emits the arguments passed to the callback function each time the callback function would be invoked.

### Parameters
`$emitter`
:   Function accepting a callback that periodically emits events.

`$onDisposed`
:   Called if the observable is disposed. The callback passed to this function is the callable provided to the $emitter callable given to this function.

`$index`
:   Position of callback function in emitter function argument list.

`...$args`
:   Other arguments to pass to emitter function.

### Return value
The new observable.


## interval()

    Observable\interval(float|int $interval, int $count = 0): Observable

Returns an observable that emits a value every `$interval` seconds, up to `$count` times (or indefinitely if `$count` is 0). The value emitted is an integer of the number of times the observable emitted a value.

### Parameters
`$interval`
:   Time interval between emitted values in seconds.

`$count`
:   Use 0 to emit values indefinitely.

### Return value
The new observable.
