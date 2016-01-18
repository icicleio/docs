Interface for readable streams.

**Extends**
:   [`Icicle\Stream\Stream`](Stream.md)


## read()

    ReadableStream::read(
        int $length = 0,
        string|null $byte = null,
        float $timeout = 0
    ): \Generator

Coroutine that is fulfilled with data read from the stream when data becomes available. If `$length` is `0`, the coroutine is fulfilled with any amount of data available on the stream. If `$length` is not `0` the coroutine will be fulfilled with a maximum of `$length` bytes, but it may be fulfilled with fewer bytes. If the `$byte` parameter is not `null`, reading will stop once the given byte is encountered in the string. The byte matched by `$byte` will be included in the fulfillment string. `$byte` should be a single byte (tip: use `chr()` to convert an integer to a single-byte string). If a multibyte string is provided, only the first byte will be used.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`int $length = 0`
:   Max number of bytes to read. Fewer bytes may be returned. Use `0` to read as much data as possible.

`string|null $byte = null`
:   Reading will stop once the given byte occurs in the stream. Note that reading may stop before the byte is found in the stream. The search byte will be included in the resolving string. Use null to effectively ignore this parameter and read any bytes.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `Icicle\Awaitable\Exception\TimeoutException` if no data is received. Use `0` for no timeout.

### Resolution value
`string`
:   Data read from the stream.

### Rejection reasons
`Icicle\Stream\Exception\UnreadableException`
:   If the stream has become unreadable. Use `isReadable()` to determine if a string is still readable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the read timed out.

`Icicle\Exception\InvalidArgumentError`
:   If the length is invalid.


## isReadable()

    ReadableStream::isReadable(): bool

### Return value
`bool`
:   `true` if the stream is readable, `false` if not.
