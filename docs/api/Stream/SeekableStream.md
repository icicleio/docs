### seek()

    SeekableStream::seek(
        int $offset,
        int $whence = SEEK_SET,
        float $timeout = 0
    ): \Generator

Moves the pointer to a new position in the stream. The `$whence` parameter is identical the parameter of the same name on the built-in `fseek()` function.

#### Parameters
`int $offset`
:   Number of bytes to seek. Usage depends on value of `$whence`.

`int $whence`
:   Values identical to `$whence` values for `fseek()` such as `\SEEK_SET`.

`float $timeout`
:   Number of seconds until the coroutine is rejected with a `Icicle\Awaitable\Exception\TimeoutException` and the stream is closed if the seek could not be performed. Use `0` for no timeout.

#### Resolution value
`int`
:   New pointer position.

#### Rejection reasons
`Icicle\Stream\Exception\UnseekableException`
:   If the stream has become unseekable. Use `isSeekable()` to determine if a stream is still seekable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the seek timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is closed while the seek is still pending.

### tell()

    SeekableStream::tell(): int

Returns the current pointer position. Value returned may not reflect the future pointer position if a read, write, or seek operation is pending.

#### Return value
`int`
:   Current pointer position (may not reflect pending seek or write operations.

### getLength()

    SeekableStream::getLength(): int

#### Return value
`int`
:   Returns the total length of the stream if known, otherwise -1. Value returned may not reflect a pending write operation.
