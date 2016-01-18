## isValid()

     ObservableIterator::isValid(): \Generator

Checks if the current position in the iterator is valid. Calling `current()` will throw an exception if the observable has completed. If an error occurs with the observable, this coroutine will be rejected with the exception used to fail the observable.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Resolution value
Resolves with `true` if a new value is available by calling [`getCurrent()`](#getcurrent) or `false` if the observable has completed.

### Throws
`Exception`
:   Exception used to fail the observable.


## getCurrent()

    ObservableIterator::getCurrent(): mixed

Gets the last emitted value or throws an exception if the observable has completed.

### Return value
Value emitted from observable.

### Throws
`Icicle\Observable\Exception\CompletedError`
:   If the observable has successfully completed.

`Icicle\Observable\Exception\UninitializedError`
:   If `isValid()` was not called before calling this method.

`Exception`
:   The exception used to fail the observable.


## getReturn()

    ObservableIterator::getReturn(): mixed

Gets the return value of the observable or throws the failure reason. Also throws an exception if the observable has not completed.

### Return value
Final return value of the observable.

### Throws
`Icicle\Observable\Exception\IncompleteError`
:   If the observable has not completed.

`Exception`
:   Exception used to fail the observable.
