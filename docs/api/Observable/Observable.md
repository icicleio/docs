An asynchronous, observable sequence of values.

An observable acts as sequence, collection, or stream of values that are emitted asynchronously over time. Like arrays or iterators, observables simplify data collections or events by allowing you to work with them asynchronously without writing numerous coroutines or long chains of awaitables.

Observables provide a number of familiar, composable operations that asynchronous versions of the simple operations commonly used on collections. Transforming methods such as [`map()`](#map) and [`filter()`](#filter) return new observables that are based on the source observable they are called on, but whose values are modified, removed, or otherwise changed based on the transformation.


## getIterator()

    Observable::getIterator(): ObservableIterator

Gets an asynchronous iterator for iterating over all of the values in the observable.

### Return value
An [`ObservableIterator`](ObservableIterator.md) that iterates over values emitted by this observable.


## dispose()

    Observable::dispose(\Exception $exception = null): void

Disposes of the observable, halting emission of values and failing the observable with the given exception.

### Parameters
`$exception`
:   An exception to fail the observable with. If no exception is given, an instance of `Icicle\Observable\Exception\DisposedException` is used.


## each()

    Observable::each(
        callable(mixed $value): \Generator|Awaitable|mixed|null $onNext = null
    ): \Generator

The given callable will be invoked each time a value is emitted from the observable. The returned awaitable will be fulfilled when the observable completes or rejected if an error occurs. The awaitable will also be rejected if `$onNext` throws an exception or returns a rejected awaitable.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`$onNext`
:   A callback function to be invoked on each value in the observable.

### Resolution value
Resolves with the value returned or resolved by the callback function.

### Throws
`Exception`
:   Throws any exception thrown by the observable, used to dispose the observable, or thrown by the callback function given to this method.


## map()

    Observable::map(
        callable(mixed $value): \Generator|Awaitable|mixed $onNext,
        callable(mixed $value): \Generator|Awaitable|mixed|null $onComplete = null
    ): Observable

Creates an observable whose values are obtained by applying a function to each value in the source observable. Each emitted value is passed to `$onNext`. The value returned from $onNext is then emitted from the returned observable. The return value of the observable is given to `$onCompleted` if provided. The return of `$onCompleted` is the return value of observable returned from this method.

### Parameters
`$onNext`
:   A function to apply to each value emitted by the observable.

`$onComplete`
:   A function to apply to the return value of the observable.

### Return value
A new observable that emits the values emitted by the source observable after applying the `$onNext` function to each one.


## filter()

    Observable::filter(
        callable(mixed $value): \Generator|Awaitable|bool) $callback
    ): Observable

Creates an observable that filters the values emitted by the observable using `$callback`. If `$callback` returns `true`, the value is emitted from the returned observable. If `$callback` returns `false`, the value is ignored and not emitted.

### Parameters
`$callback`
:   A function to filter the values with.

### Return value
A new observable that filters the values emitted by the source observable.


## throttle()

    Observable::throttle(float|int $time): Observable

Throttles the observable to only emit a value every `$time` seconds.

### Parameters
`$time`
:   The amount of time to delay between emitting values in seconds.

### Return value
A new observable that throttles the values emitted by the source observable.


## splat()

    Observable::splat(
        callable(mixed ...$args): mixed $onNext,
        callable(mixed ...$args): mixed|null $onComplete = null
    ): Observable

This method is a modified form of [`map()`](#map) that expects the observable to emit an array or Traversable that is used as arguments to the given callback function. The array is key sorted before being used as function arguments. If the observable does not emit an array or Traversable, the observable will error with an instance of `Icicle\Observable\Exception\InvalidArgumentError`.

### Parameters
`$onNext`
:   A function to apply to each group of values emitted by the observable.

`$onComplete`
:   A function to apply to the return value of the observable.

### Return value
A new observable that emits values returned by the `$onNext` function applied to each group of values in the source observable.


## take()

    Observable::take(int $count): Observable

Only emits the next `$count` values from the observable before completing.

### Parameters
`$count`
:   The number of values to take.

### Return value
A new observable that emits the first `$count` values from the source observable.


## skip()

    Observable::skip(int $count): Observable

Skips the first `$count` values emitted from the observable.

### Parameters
`$count`
:   The number of values to skip.

### Return value
A new observable that emits values emitted from the source observable after the `$count`-nth value.


## isComplete()

    Observable::isComplete(): bool

Determines if the observable has completed (completed observables will no longer emit values). Returns `true` even if the observable completed due to an error.


## isFailed()

    Observable::isFailed(): bool

Determines if the observable has completed due to an error.
