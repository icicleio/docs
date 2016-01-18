A buffered reader that reads text from a stream.

This class wraps a readable stream and provides methods for reading in a buffered, encoding-aware way. All reads are buffered, which improves performance on small reads and allows peeking data with [`peek()`](#peek).

Instead of reading based on single bytes, `TextReader` reads data in characters in a specified encoding. Using a `TextReader` to read encoded text from a stream can prevent bugs that occur when working with multibyte characters, which could be split between raw reads and result in a malformed string. This class uses the internal buffer to store incomplete characters as they are read so that reads can return valid encoded strings without data loss.

!!! note
    Requires the [mbstring](http://php.net/manual/en/book.mbstring.php) extension to be loaded.


## __construct()

    new TextReader(ReadableStream $stream, string $encoding = 'UTF-8')

Creates a new stream reader for a given stream.

### Parameters
`$stream`
:   A readable stream to read from.

`$encoding`
:   The character encoding to use. The character encoding should be set to match the encoding expected to be read from the stream. The default encoding to use is UTF-8.

### Throws
`Icicle\Exception\UnsupportedError`
:   Thrown if the mbstring extension is not loaded.


## getStream()

    TextReader::getStream(): ReadableStream

Gets the underlying stream.


## isOpen()

    TextReader::isOpen(): bool

Determines if the stream is still open.


## close()

    TextReader::close(): void

Closes the stream reader and the underlying stream.


## peek()

    TextReader::peek(
        int $length = 1,
        float $timeout = 0
    ): \Generator

Reads the next sequence of characters without consuming them.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`int $length`
:   The number of characters to peek.

`float|int $timeout`
:   Number of seconds until the returned promise is rejected with a `TimeoutException` if no data is received. Use 0 for no timeout.

### Resolution value
`string`
:   String of characters read from the stream.

### Rejection reasons
`Icicle\Stream\Exception\UnreadableException`
:   If the stream has become unreadable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the read timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is unexpectedly closed.


## read()

    TextReader::read(
        int $length = 1,
        float $timeout = 0
    ): \Generator

Reads a specific number of characters from the stream.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`int $length = 0`
:   The number of characters to read.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `TimeoutException` if no data is received. Use `0` for no timeout.

### Resolution value
`string`
:   String of characters read from the stream.

### Rejection reasons
`Icicle\Stream\Exception\UnreadableException`
:   If the stream has become unreadable. Use `isReadable()` to determine if a string is still readable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the read timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is unexpectedly closed.


## readLine()

    TextReader::readLine(float $timeout = 0): \Generator

Reads a single line from the stream.

Reads from the stream until a newline is reached or the stream is closed. The newline characters are included in the returned string. If reading ends in the middle of a character, the trailing bytes are not consumed.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `TimeoutException` if no data is received. Use `0` for no timeout.

### Resolution value
`string`
:   A line of text read from the stream.

### Rejection reasons
`Icicle\Stream\Exception\UnreadableException`
:   If the stream has become unreadable. Use `isReadable()` to determine if a string is still readable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the read timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is unexpectedly closed.


## readAll()

    TextReader::readAll(
        int $maxlength = 0,
        float $timeout = 0
    ): \Generator

Reads all characters from the stream until the end of the stream is reached.

If the stream ends in the middle of a character, the left over bytes will be discarded.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`int $maxlength = 0`
:   Max number of bytes to read. Use `0` to read as much data as possible.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `TimeoutException` if no data is received. Use `0` for no timeout.

### Resolution value
`string`
:   String of characters read from the stream.

### Rejection reasons
`Icicle\Stream\Exception\UnreadableException`
:   If the stream has become unreadable. Use `isReadable()` to determine if a string is still readable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the read timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is unexpectedly closed.


## scan()

    TextReader::scan(
        string $format,
        float $timeout = 0
    ): \Generator

Reads and parses characters from the stream according to a format.

This method is similar to the standard [`fscanf()` function](http://php.net/fscanf), but parses input lazily and works on asynchronous streams.

The format string is of the same format as `fscanf()`. See the [`sprintf()` documentation](http://php.net/sprintf) for a list of format specifiers.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`string $format`
:   The parse format.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `TimeoutException` if no data is received. Use `0` for no timeout.

### Resolution value
`array`
:   An array of parsed values.

### Rejection reasons
`Icicle\Stream\Exception\UnreadableException`
:   If the stream has become unreadable. Use `isReadable()` to determine if a string is still readable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If the read timed out.

`Icicle\Stream\Exception\ClosedException`
:   If the stream is unexpectedly closed.
