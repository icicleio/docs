This optional package provides an asynchronous readable, writable, and seekable stream interfaces and a couple basic stream implementations. The package is available on [Packagist](https://packagist.org) as [`icicleio/stream`](https://packagist.org/packages/icicleio/stream).

Streams represent a common awaitable-based API that may be implemented by classes that read or write sequences of binary data to facilitate interoperability. The stream component defines three interfaces, one of which should be used by all streams.

- `Icicle\Stream\ReadableStream`: Interface to be used by streams that are only readable.
- `Icicle\Stream\WritableStream`: Interface to be used by streams that are only writable.
- `Icicle\Stream\DuplexStream`: Interface to be used by streams that are readable and writable. Extends both `Icicle\Stream\ReadableStream` and `Icicle\Stream\WritableStream`.
- `Icicle\Stream\SeekableStream`: Interface to be used by seekable streams (readable and/or writable).


## pipe()

    Icicle\Stream\pipe(
        Icicle\Stream\ReadableStream $source
        Icicle\Stream\WritableStream $destination,
        bool $end = true,
        int $length = 0,
        string|null $byte = null
        float $timeout = 0
    ): \Generator

Returns a generator that should be used within a coroutine or used to create a new coroutine. Pipes all data read from this stream to the writable stream. If `$length` is not `0`, only `$length` bytes will be piped to the writable stream. The returned awaitable is fulfilled with the number of bytes piped once the writable stream is no longer writable, `$length` bytes have been piped, or `$byte` is encountered in the stream.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`Icicle\Stream\ReadableStream $source`
:   A readable stream to pipe data from.

`Icicle\Stream\WritableStream $destination`
:   A writable stream to pipe data to. All data from `$source` will be written to `$dest` as it becomes readable.

`bool $end = true`
:   Indicates if the destination stream should be closed when there is no more data in `$source`.

`int $length = 0`
:   The maximum number of bytes to pipe from `$source` to `$dest`.

`string|null $byte = null`
:   If `$byte` is not `null`, piping will end once `$byte` is encountered in the stream.

`float $timeout = 0`
:   Number of seconds between successful read or write operations until the coroutine is rejected with a `Icicle\Awaitable\Exception\TimeoutException` and the destination stream is closed if the data cannot be written to the stream. Use `0` for no timeout.

### Resolution value
`int`
:   Number of bytes read from the source stream.

### Rejection reasons
`Icicle\Stream\Exception\UnreadableException`
:   If the source stream has become unreadable.

`Icicle\Stream\Exception\UnwritableException`
:   If the destination stream has become unreadable.

`Icicle\Awaitable\Exception\TimeoutException`
:   If a read or write operation timed out.

`Icicle\Exception\InvalidArgumentError`
:   If the length is invalid.


## readTo()

    Icicle\Stream\readTo(
        Icicle\Stream\ReadableStream $stream
        int $length,
        float $timeout = 0
    ): \Generator

Coroutine that reads data from the given readable stream until the given number of bytes has been read from the stream.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`Icicle\Stream\ReadableStream $stream`
:   A readable stream to read from.

`int $length`
:   The maximum number of bytes to read from `$source`.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `Icicle\Awaitable\Exception\TimeoutException`. Use `0` for no timeout.

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


## readUntil()

    Icicle\Stream\readUntil(
        Icicle\Stream\ReadableStream $stream
        string $needle,
        int $maxLength = 0,
        float $timeout = 0
    ): \Generator

Coroutine that reads data from the given readable stream until the given string of bytes is read from the stream or the max length is reached. The matched string of bytes is included in the result string.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`Icicle\Stream\ReadableStream $stream`
:   A readable stream to read from.

`string $needle`
:   The string to match against while reading.

`int $maxLength = 0`
:   The maximum number of bytes to read from `$source`.

`float $timeout = 0`
:   Number of seconds until the coroutine is rejected with a `Icicle\Awaitable\Exception\TimeoutException`. Use `0` for no timeout.

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


## readAll()

    Icicle\Stream\readAll(
        Icicle\Stream\ReadableStream $stream
        int $maxlength = 0,
        float $timeout = 0
    ): \Generator

Coroutine that reads data from the given readable stream until stream is no longer readable or the max length is reached.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`Icicle\Stream\ReadableStream $stream`
:   A readable stream to read from.

`int $maxLength = 0`
:   The maximum number of bytes to read from `$stream`. Use `0` for no max length.

`float $timeout = 0`
:   Number of seconds until the returned awaitable is rejected with a `TimeoutException` and the stream is closed if the data cannot be written to the stream. Use `0` for no timeout.

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


## pair()

    Icicle\Stream\pair(): resource[]

### Return value
`[resource, resource]`
:   Returns an array containing a pair of connected stream socket resources.


## stdin()

    Icicle\Stream\stdin(): Icicle\Stream\ReadableStream

### Return value
`Icicle\Stream\ReadableStream`
:   Returns a global readable stream instance for `STDIN`.


## stdout()

    Icicle\Stream\stdout(): Icicle\Stream\WritableStream

### Return value
`Icicle\Stream\WritableStream`
:   Returns a global writable stream instance for `STDOUT`.


## stderr()

    Icicle\Stream\stderr(): Icicle\Stream\WritableStream

### Return value
`Icicle\Stream\WritableStream`
:   Returns a global writable stream instance for `STDERR`.
