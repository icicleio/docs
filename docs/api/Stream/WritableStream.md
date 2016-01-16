### write()

    WritableStream::write(
        string $data,
        float $timeout = 0
    ): \Generator

Writes the given data to the stream. Returns an awaitable that is fulfilled with the number of bytes written once that data has successfully been written to the stream.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`string $data`
:   The data to write to the stream.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `Icicle\Awaitable\Exception\TimeoutException` and the stream is closed if the data cannot be written to the stream. Use `0` for no timeout.

#### Resolution value
`int`
:   Number of bytes written to the stream.

#### Rejection reasons
`Icicle\Stream\Exception\UnwritableException`
:   If the stream has become unwritable. Use `isWritable()` to determine if a stream is still writable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the write timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is closed while the write is still pending.

### end()

    WritableStream::end(
        string $data = '',
        float $timeout = 0
    ): \Generator

Closes the stream once the data has been successfully written to the stream. Immediately makes the stream unwritable.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`string $data = ''`
:   The data to write to the stream.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `Icicle\Awaitable\Exception\TimeoutException` and the stream is closed if the data cannot be written to the stream. Use `0` for no timeout.

#### Resolution value
`int`
:   Number of bytes written to the stream.

#### Rejection reasons
`Icicle\Stream\Exception\UnwritableException`
:   If the stream has become unwritable. Use `isWritable()` to determine if a stream is still writable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the write timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is closed while the write is still pending.

### isWritable()

    WritableStream::isWritable(): bool

#### Return value
`bool`
:   `true` if the stream is writable, `false` if not.
