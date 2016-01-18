A buffered writer that writes text to a stream.


## __construct()

    new TextWriter(
        WritableStream $stream,
        float $timeout = 0,
        string $encoding = 'UTF-8',
        bool $autoFlush = false,
        int $bufferSize = self::DEFAULT_BUFFER_SIZE
    )

Creates a new stream writer for a given stream.

### Parameters
`Icicle\Stream\WritableStream $stream`
:   The stream to write to.

`float|int $timeout`
:   The timeout for write operations. Use 0 for no timeout.

`string $encoding`
:   The character encoding to use. The character encoding should be set to match the encoding of the text being written. The default encoding to use is UTF-8.

`bool $autoFlush`
:   Indicates if the buffer should be flushed on every write.

`int $bufferSize`
:   The max buffer size in bytes.


## getStream()

    TextWriter::getStream(): WritableStream

Gets the underlying stream.


## isOpen()

    TextWriter::isOpen(): bool

Determines if the stream is still open.


## close()

    TextWriter::close(): void

Closes the stream writer and the underlying stream.


## flush()

    flush(): \Generator

Flushes the contents of the internal buffer to the underlying stream.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Resolution value
`int`
:   Number of bytes written to the stream.

### Rejection reasons
`Icicle\Stream\Exception\UnwritableException`
:   If the stream has become unwritable. Use `isWritable()` to determine if a stream is still writable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the write timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is closed while the write is still pending.


## write()

    TextWriter::write(string $text): \Generator

Writes a value to the stream.

The given value will be coerced to a string before being written. The resulting string will be written to the internal buffer; if the buffer is full, the entire buffer will be flushed to the stream.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`mixed $text`
:   A printable value that can be coerced to a string.

### Resolution value
`int`
:   Number of bytes written to the stream.

### Rejection reasons
`Icicle\Stream\Exception\UnwritableException`
:   If the stream has become unwritable. Use `isWritable()` to determine if a stream is still writable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the write timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is closed while the write is still pending.


## writeLine()

    TextWriter::writeLine(string $text): \Generator

Writes a value to the stream and then terminates the line.

The given value will be coerced to a string before being written.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`mixed $text`
:   A printable value that can be coerced to a string.

### Resolution value
`int`
:   Number of bytes written to the stream.

### Rejection reasons
`Icicle\Stream\Exception\UnwritableException`
:   If the stream has become unwritable. Use `isWritable()` to determine if a stream is still writable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the write timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is closed while the write is still pending.


## printf()

    TextWriter::printf(string $format, ...$args): \Generator

Writes a formatted string to the stream.

Accepts a format string followed by a series of mixed values. The string will be formatted in accordance with the specification of the built-in function [`printf()`](http://php.net/printf).

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`string $format`
:   The format string.

`...$args`
:   A list of values to format.

### Resolution value
`int`
:   Number of bytes written to the stream.

### Rejection reasons
`Icicle\Stream\Exception\UnwritableException`
:   If the stream has become unwritable. Use `isWritable()` to determine if a stream is still writable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the write timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is closed while the write is still pending.


## printLine()

    TextWriter::printLine(string $format, ...$args): \Generator

Writes a formatted string to the stream and then terminates the line.

Accepts a format string followed by a series of mixed values. The string will be formatted in accordance with the specification of the built-in function [`printf()`](http://php.net/printf).

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`string $format`
:   The format string.

`...$args`
:   A list of values to format.

### Resolution value
`int`
:   Number of bytes written to the stream.

### Rejection reasons
`Icicle\Stream\Exception\UnwritableException`
:   If the stream has become unwritable. Use `isWritable()` to determine if a stream is still writable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the write timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is closed while the write is still pending.
