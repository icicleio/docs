This optional package provides an asynchronous readable, writable, and seekable stream interfaces and a couple basic stream implementations. The package is available on [Packagist](https://packagist.org) as [`icicleio/stream`](https://packagist.org/packages/icicleio/stream).

Streams represent a common awaitable-based API that may be implemented by classes that read or write sequences of binary data to facilitate interoperability. The stream component defines three interfaces, one of which should be used by all streams.

- `Icicle\Stream\ReadableStream`: Interface to be used by streams that are only readable.
- `Icicle\Stream\WritableStream`: Interface to be used by streams that are only writable.
- `Icicle\Stream\DuplexStream`: Interface to be used by streams that are readable and writable. Extends both `Icicle\Stream\ReadableStream` and `Icicle\Stream\WritableStream`.
- `Icicle\Stream\SeekableStream`: Interface to be used by seekable streams (readable and/or writable).


## Stream

All stream interfaces extend this basic interface.

### isOpen()

    Stream::isOpen(): bool

#### Return value
`bool`
:   `true` if the stream is still open, `false` if not.

### close()

    Stream::close(): void

Closes the stream. Once closed, a stream will no longer be readable or writable.

## ReadableStream

### read()

    ReadableStream::read(
        int $length = 0,
        string|null $byte = null,
        float $timeout = 0
    ): \Generator

Coroutine that is fulfilled with data read from the stream when data becomes available. If `$length` is `0`, the coroutine is fulfilled with any amount of data available on the stream. If `$length` is not `0` the coroutine will be fulfilled with a maximum of `$length` bytes, but it may be fulfilled with fewer bytes. If the `$byte` parameter is not `null`, reading will stop once the given byte is encountered in the string. The byte matched by `$byte` will be included in the fulfillment string. `$byte` should be a single byte (tip: use `chr()` to convert an integer to a single-byte string). If a multibyte string is provided, only the first byte will be used.

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`int $length = 0`
:   Max number of bytes to read. Fewer bytes may be returned. Use `0` to read as much data as possible.

`string|null $byte = null`
:   Reading will stop once the given byte occurs in the stream. Note that reading may stop before the byte is found in the stream. The search byte will be included in the resolving string. Use null to effectively ignore this parameter and read any bytes.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `\Icicle\Awaitable\Exception\TimeoutException` if no data is received. Use `0` for no timeout.

#### Resolution value
`string`
:   Data read from the stream.

#### Rejection reasons
`\Icicle\Stream\Exception\UnreadableException`
:   If the stream has become unreadable. Use `isReadable()` to determine if a string is still readable.

`\Icicle\Awaitable\Exception\TimeoutException`
:   If the read timed out.

`\Icicle\Exception\InvalidArgumentError`
:   If the length is invalid.

### isReadable()

    ReadableStream::isReadable(): bool

#### Return value
`bool`
:   `true` if the stream is readable, `false` if not.

## WritableStream

### write()

    WritableStream::write(
        string $data,
        float $timeout = 0
    ): \Generator

Writes the given data to the stream. Returns an awaitable that is fulfilled with the number of bytes written once that data has successfully been written to the stream.

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`string $data`
:   The data to write to the stream.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `\Icicle\Awaitable\Exception\TimeoutException` and the stream is closed if the data cannot be written to the stream. Use `0` for no timeout.

#### Resolution value
`int`
:   Number of bytes written to the stream.

#### Rejection reasons
`\Icicle\Stream\Exception\UnwritableException`
:   If the stream has become unwritable. Use `isWritable()` to determine if a stream is still writable.

`\Icicle\Awaitable\Exception\TimeoutException`
:   If the write timed out.

`\Icicle\Stream\Exception\ClosedException`
:   If the stream is closed while the write is still pending.

### end()

    WritableStream::end(
        string $data = '',
        float $timeout = 0
    ): \Generator

Closes the stream once the data has been successfully written to the stream. Immediately makes the stream unwritable.

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`string $data = ''`
:   The data to write to the stream.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `\Icicle\Awaitable\Exception\TimeoutException` and the stream is closed if the data cannot be written to the stream. Use `0` for no timeout.

#### Resolution value
`int`
:   Number of bytes written to the stream.

#### Rejection reasons
`\Icicle\Stream\Exception\UnwritableException`
:   If the stream has become unwritable. Use `isWritable()` to determine if a stream is still writable.

`\Icicle\Awaitable\Exception\TimeoutException`
:   If the write timed out.

`\Icicle\Stream\Exception\ClosedException`
:   If the stream is closed while the write is still pending.

### isWritable()

    WritableStream::isWritable(): bool

#### Return value
`bool`
:   `true` if the stream is writable, `false` if not.


## DuplexStream

A duplex stream is both readable and writable. `Icicle\Stream\DuplexStream` extends both `Icicle\Stream\ReadableStream` and `Icicle\Stream\WritableStream`, and therefore inherits all the methods above.


## SeekableStream

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
:   Number of seconds until the coroutine is rejected with a `\Icicle\Awaitable\Exception\TimeoutException` and the stream is closed if the seek could not be performed. Use `0` for no timeout.

#### Resolution value
`int`
:   New pointer position.

#### Rejection reasons
`\Icicle\Stream\Exception\UnseekableException`
:   If the stream has become unseekable. Use `isSeekable()` to determine if a stream is still seekable.

`\Icicle\Awaitable\Exception\TimeoutException`
:   If the seek timed out.

`\Icicle\Stream\Exception\ClosedException`
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


## MemoryStream

`Icicle\Stream\MemoryStream` objects act as a buffer that implements `Icicle\Stream\DuplexStream`, allowing consumers to be notified when data is available in the buffer. This class by itself is not particularly useful, but it can be extended to add functionality upon reading or writing, as well as acting as an example of how stream classes can be implemented.

Anything written to an instance of `Icicle\Stream\MemoryStream` is immediately readable.

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Loop;
use Icicle\Stream\MemoryStream;

$stream = new MemoryStream();

$generator = function ($stream) {
    yield $stream->write("This is just a test.\nThis will not be read.");

    $data = (yield $stream->read(0, "\n"));

    echo $data; // Echoes "This is just a test."
};

$coroutine = new Coroutine($generator($stream));

Loop\run();
```


## MemorySink

`Icicle\Stream\MemorySink` acts as a buffered sink with a seekable read/write pointer. All data written to the sink remains in the sink. The read/write pointer may be moved anywhere within the buffered sink using `seek()`. The current position of the pointer may be determined with `tell()`. Since all data remains in the sink, the entire length of the sink is available with `getLength()`.

```php
use Icicle\Coroutine;
use Icicle\Loop;
use Icicle\Stream\MemorySink;

$coroutine = Coroutine\create(function () {
    $sink = new MemorySink();

    yield $sink->write("This is just a test.\n");

    yield $sink->seek(15);

    yield $sink->write("sink ");

    yield $sink->seek(0);

    yield $sink->read(0, "\n"); // Last `yield` acts like `return`
});

echo $coroutine->wait(); // Echoes "This is just a sink test."
```

## Resource

All stream resource (pipe) classes in this package (and some other packages suck as [socket](https://github.com/icicleio/socket)) implement `Icicle\Stream\Resource`.

### getResource()

    Resource::getResource(): resource

#### Return value
`resource`
:   Returns the underlying PHP stream resource.

### isOpen()

    Resource::isOpen(): bool

#### Return value
`bool`
:   `true` if the resource is still open, `false` if not.

### close()

    Resource::close(): void

Closes the stream resource, making it unreadable or unwritable.


## ReadablePipe

`Icicle\Stream\Pipe\ReadablePipe` implements `Icicle\Stream\ReadableStream`, so it is interoperable with any other class implementing one of the stream interfaces.

When the other end of the connection is closed and a read is pending, that read will be fulfilled with an empty string. Subsequent reads will then reject with an instance of `Icicle\Stream\Exception\UnreadableException` and `isReadable()` will return `false`.


### ReadablePipe Constructor

    $stream = new ReadablePipe(resource $resource, bool $autoClose = true)

Creates a readable stream from the given stream resource (note only stream resources created from pipes and sockets are supported, *not* file streams).


## WritablePipe

`Icicle\Stream\Pipe\WritablePipe` implements `Icicle\Stream\WritableStream`, so it is interoperable with any other class implementing one of the stream interfaces.

### WritablePipe Constructor

    $stream = new WritablePipe(resource $resource, bool $autoClose = true)

Creates a writable stream from the given stream resource (note only stream resources created from pipes and sockets are supported, *not* file streams).


## DuplexPipe

`Icicle\Stream\Pipe\DuplexPipe` implements `Icicle\Stream\DuplexStream`, making it both a readable stream and a writable stream.

### DuplexPipe Constructor

    $stream = new DuplexPipe(resource $resource, bool $autoClose = true)

Creates a duplex stream from the given stream resource (note only stream resources created from pipes and sockets are supported, *not* file streams).


## Functions

### pipe()

    \Icicle\Stream\pipe(
        \Icicle\Stream\ReadableStream $source
        \Icicle\Stream\WritableStream $destination,
        bool $end = true,
        int $length = 0,
        string|null $byte = null
        float $timeout = 0
    ): \Generator

Returns a generator that should be used within a coroutine or used to create a new coroutine. Pipes all data read from this stream to the writable stream. If `$length` is not `0`, only `$length` bytes will be piped to the writable stream. The returned awaitable is fulfilled with the number of bytes piped once the writable stream is no longer writable, `$length` bytes have been piped, or `$byte` is encountered in the stream.

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`\Icicle\Stream\ReadableStream $source`
:   A readable stream to pipe data from.

`\Icicle\Stream\WritableStream $destination`
:   A writable stream to pipe data to. All data from `$source` will be written to `$dest` as it becomes readable.

`bool $end = true`
:   Indicates if the destination stream should be closed when there is no more data in `$source`.

`int $length = 0`
:   The maximum number of bytes to pipe from `$source` to `$dest`.

`string|null $byte = null`
:   If `$byte` is not `null`, piping will end once `$byte` is encountered in the stream.

`float $timeout = 0`
:   Number of seconds between successful read or write operations until the coroutine is rejected with a `\Icicle\Awaitable\Exception\TimeoutException` and the destination stream is closed if the data cannot be written to the stream. Use `0` for no timeout.

#### Resolution value
`int`
:   Number of bytes read from the source stream.

#### Rejection reasons
`\Icicle\Stream\Exception\UnreadableException`
:   If the source stream has become unreadable.

`\Icicle\Stream\Exception\UnwritableException`
:   If the destination stream has become unreadable.

`\Icicle\Awaitable\Exception\TimeoutException`
:   If a read or write operation timed out.

`\Icicle\Exception\InvalidArgumentError`
:   If the length is invalid.

### readTo()

    \Icicle\Stream\readTo(
        \Icicle\Stream\ReadableStream $stream
        int $length,
        float $timeout = 0
    ): \Generator

Coroutine that reads data from the given readable stream until the given number of bytes has been read from the stream.

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`\Icicle\Stream\ReadableStream $stream`
:   A readable stream to read from.

`int $length`
:   The maximum number of bytes to read from `$source`.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `\Icicle\Awaitable\Exception\TimeoutException`. Use `0` for no timeout.

#### Resolution value
`string`
:   Data read from the stream.

#### Rejection reasons
`\Icicle\Stream\Exception\UnreadableException`
:   If the stream has become unreadable. Use `isReadable()` to determine if a string is still readable.

`\Icicle\Awaitable\Exception\TimeoutException`
:   If the read timed out.

`\Icicle\Exception\InvalidArgumentError`
:   If the length is invalid.

### readUntil()

    \Icicle\Stream\readUntil(
        \Icicle\Stream\ReadableStream $stream
        string $needle,
        int $maxLength = 0,
        float $timeout = 0
    ): \Generator

Coroutine that reads data from the given readable stream until the given string of bytes is read from the stream or the max length is reached. The matched string of bytes is included in the result string.

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`\Icicle\Stream\ReadableStream $stream`
:   A readable stream to read from.

`string $needle`
:   The string to match against while reading.

`int $maxLength = 0`
:   The maximum number of bytes to read from `$source`.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `\Icicle\Awaitable\Exception\TimeoutException`. Use `0` for no timeout.

#### Resolution value
`string`
:   Data read from the stream.

#### Rejection reasons
`\Icicle\Stream\Exception\UnreadableException`
:   If the stream has become unreadable. Use `isReadable()` to determine if a string is still readable.

`\Icicle\Awaitable\Exception\TimeoutException`
:   If the read timed out.

`\Icicle\Exception\InvalidArgumentError`
:   If the length is invalid.

### readAll()

    \Icicle\Stream\readAll(
        \Icicle\Stream\ReadableStream $stream
        int $maxlength = 0,
        float $timeout = 0
    ): \Generator

Coroutine that reads data from the given readable stream until stream is no longer readable or the max length is reached.

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`\Icicle\Stream\ReadableStream $stream`
:   A readable stream to read from.

`int $maxLength = 0`
:   The maximum number of bytes to read from `$stream`. Use `0` for no max length.

`float $timeout = 0`
:   Number of seconds until the returned awaitable is rejected with a `TimeoutException` and the stream is closed if the data cannot be written to the stream. Use `0` for no timeout.

#### Resolution value
`string`
:   Data read from the stream.

#### Rejection reasons
`\Icicle\Stream\Exception\UnreadableException`
:   If the stream has become unreadable. Use `isReadable()` to determine if a string is still readable.

`\Icicle\Awaitable\Exception\TimeoutException`
:   If the read timed out.

`\Icicle\Exception\InvalidArgumentError`
:   If the length is invalid.

### pair()

    Icicle\Stream\pair(): resource[]

#### Return value
`[resource, resource]`
:   Returns an array containing a pair of connected stream socket resources.

### stdin()

    Icicle\Stream\stdin(): \Icicle\Stream\ReadableStream

#### Return value
`\Icicle\Stream\ReadableStream`
:   Returns a global readable stream instance for STDIN.

### stdout()

    Icicle\Stream\stdout(): \Icicle\Stream\WritableStream

#### Return value
`\Icicle\Stream\WritableStream`
:   Returns a global writable stream instance for STDOUT.

### stderr()

    Icicle\Stream\stderr(): \Icicle\Stream\WritableStream

#### Return value
`\Icicle\Stream\WritableStream`
:   Returns a global writable stream instance for STDERR.
