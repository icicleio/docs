This library is a component for Icicle, providing an asynchronous readable, writable, and seekable stream interfaces and a couple basic stream implementations.

[![Build Status](https://img.shields.io/travis/icicleio/stream/v1.x.svg?style=flat-square)](https://travis-ci.org/icicleio/stream)
[![Coverage Status](https://img.shields.io/coveralls/icicleio/stream/v1.x.svg?style=flat-square)](https://coveralls.io/r/icicleio/stream)
[![Semantic Version](https://img.shields.io/github/release/icicleio/stream.svg?style=flat-square)](http://semver.org)
[![Apache 2 License](https://img.shields.io/packagist/l/icicleio/stream.svg?style=flat-square)](LICENSE)
[![@icicleio on Twitter](https://img.shields.io/badge/twitter-%40icicleio-5189c7.svg?style=flat-square)](https://twitter.com/icicleio)

##### Requirements

- PHP 5.5+

##### Installation

The recommended way to install is with the [Composer](http://getcomposer.org/) package manager. (See the [Composer installation guide](https://getcomposer.org/doc/00-intro.md) for information on installing and using Composer.)

Run the following command to use this library in your project:

```bash
composer require icicleio/stream
```

You can also manually edit `composer.json` to add this library as a project requirement.

```js
// composer.json
{
    "require": {
        "icicleio/stream": "^0.3"
    }
}
```

Streams represent a common promise-based API that may be implemented by classes that read or write sequences of binary data to facilitate interoperability. The stream component defines three interfaces, one of which should be used by all streams.

- `Icicle\Stream\ReadableStreamInterface`: Interface to be used by streams that are only readable.
- `Icicle\Stream\WritableStreamInterface`: Interface to be used by streams that are only writable.
- `Icicle\Stream\DuplexStreamInterface`: Interface to be used by streams that are readable and writable. Extends both `Icicle\Stream\ReadableStreamInterface` and `Icicle\Stream\WritableStreamInterface`.
- `Icicle\Stream\SeekableStreamInterface`: Interface to be used by seekable streams (readable and/or writable).

## StreamInterface

All other stream interfaces extend this basic interface.

#### isOpen()

```php
StreamInterface::isOpen(): bool
```

Determines if the stream is still open. A closed stream will be neither readable or writable.

---

#### close()

```php
StreamInterface::close(): void
```

Closes the stream. Once closed, a stream will no longer be readable or writable.

## ReadableStreamInterface

#### read()

```php
ReadableStreamInterface::read(
    int $length = 0,
    string|null $byte = null,
    float $timeout = 0
): Generator
```

Returns a promise that is fulfilled with data read from the stream when data becomes available. If `$length` is `0`, the promise is fulfilled with any amount of data available on the stream. If `$length` is not `0` the promise will be fulfilled with a maximum of `$length` bytes, but it may be fulfilled with fewer bytes. If the `$byte` parameter is not `null`, reading will stop once the given byte is encountered in the string. The byte matched by `$byte` will be included in the fulfillment string. `$byte` should be a single byte (tip: use `chr()` to convert an integer to a single-byte string). If a multibyte string is provided, only the first byte will be used.

##### Parameters
`$length`
:   Max number of bytes to read. Fewer bytes may be returned. Use 0 to read as much data as possible.

`$byte`
:   Reading will stop once the given byte occurs in the stream. Note that reading may stop before the byte is found in the stream. The search byte will be included in the resolving string. Use null to effectively ignore this parameter and read any bytes.

`$timeout`
:   Number of seconds until the returned promise is rejected with a `TimeoutException` if no data is received. Use 0 for no timeout.

Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `string` | Any number of bytes or up to `$length` bytes if `$length` was not `0`.
Rejected | `Icicle\Stream\Exception\BusyError` | If a read was already pending on the stream.
Rejected | `Icicle\Stream\Exception\UnreadableException` | If the stream is no longer readable.
Rejected | `Icicle\Stream\Exception\ClosedException` | If the stream is unexpectedly closed.
Rejected | `Icicle\Promise\Exception\TimeoutException` | If reading from the stream times out.

---

#### isReadable()

```php
ReadableStreamInterface::isReadable(): bool
```

Determines if the stream is readable.

## WritableStreamInterface

#### write()

```php
WritableStreamInterface::write(
    string $data,
    float $timeout = 0
): Generator
```

Writes the given data to the stream. Returns a promise that is fulfilled with the number of bytes written once that data has successfully been written to the stream.

##### Parameters
`$data`
:   The data to write to the stream.

`$timeout`
:   Number of seconds until the returned promise is rejected with a `TimeoutException` and the stream is closed if the data cannot be written to the stream. Use 0 for no timeout.

Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `int` | Fulfilled with the number of bytes written when the data has actually been written to the stream.
Rejected | `Icicle\Stream\Exception\UnwritableException` | If the stream is no longer writable.
Rejected | `Icicle\Stream\Exception\ClosedException` | If the stream is unexpectedly closed.
Rejected | `Icicle\Promise\Exception\TimeoutException` | If writing to the stream times out.

---

#### end()

```php
WritableStreamInterface::end(
    string $data = '',
    float $timeout = 0
): Generator
```

Writes the given data to the stream then immediately closes the stream by calling `close()`.

##### Parameters
`$data`
:   The data to write to the stream.

`$timeout`
:   Number of seconds until the returned promise is rejected with a `TimeoutException` and the stream is closed if the data cannot be written to the stream. Use 0 for no timeout.

Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `int` | Fulfilled with the number of bytes written when the data has actually been written to the stream.
Rejected | `Icicle\Stream\Exception\UnwritableException` | If the stream is no longer writable.
Rejected | `Icicle\Stream\Exception\ClosedException` | If the stream is unexpectedly closed.
Rejected | `Icicle\Promise\Exception\TimeoutException` | If writing to the stream times out.

---

#### isWritable()

```php
WritableStreamInterface::isWritable(): bool
```

Determines if the stream is writable.

## DuplexStreamInterface

A duplex stream is both readable and writable. `Icicle\Stream\DuplexStreamInterface` extends both `Icicle\Stream\ReadableStreamInterface` and `Icicle\Stream\WritableStreamInterface`, and therefore inherits all the methods above.

## SeekableStreamInterface

#### seek()

```php
SeekableStreamInterface::seek(
    int $offset,
    int $whence = SEEK_SET,
    float $timeout = 0
): Generator
```

Moves the pointer to a new position in the stream. The `$whence` parameter is identical the parameter of the same name on the built-in `fseek()` function.

##### Parameters
`$offset`
:   Number of bytes to seek. Usage depends on value of `$whence`.

`$whence`
:   Values identical to `$whence` values for `fseek()`.

`$timeout`
:   Number of seconds until the returned promise is rejected with a `TimeoutException` and the stream is closed if the data cannot be written to the stream. Use 0 for no timeout.

Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `int` | Fulfilled with the new pointer position.
Rejected | `Icicle\Stream\Exception\UnseekableException` | If the stream is no longer seekable.
Rejected | `Icicle\Stream\Exception\ClosedException` | If the stream is unexpectedly closed.
Rejected | `Icicle\Promise\Exception\TimeoutException` | If seeking times out.

---

#### tell()

```php
SeekableStreamInterface::tell(): int
```

Returns the current pointer position. Value returned may not reflect the future pointer position if a read, write, or seek operation is pending.

Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `int` | Fulfilled with the number of bytes written when the data has actually been written to the stream.
Rejected | `Icicle\Stream\Exception\UnwritableException` | If the stream is no longer writable.
Rejected | `Icicle\Stream\Exception\ClosedException` | If the stream is unexpectedly closed.
Rejected | `Icicle\Promise\Exception\TimeoutException` | If writing to the stream times out.

---

#### getLength()

```php
SeekableStreamInterface::getLength(): int
```

Returns the total length of the stream if known, otherwise -1. Value returned may not reflect a pending write operation.

## MemoryStream

`Icicle\Stream\MemoryStream` objects act as a buffer that implements `Icicle\Stream\DuplexStreamInterface`, allowing consumers to be notified when data is available in the buffer. This class by itself is not particularly useful, but it can be extended to add functionality upon reading or writing, as well as acting as an example of how stream classes can be implemented.

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

## StreamResourceInterface

All stream resource (pipe) classes in this package (and some other packages suck as [socket](https://github.com/icicleio/socket)) implement `Icicle\Stream\StreamResourceInterface`. This interface extends `Icicle\Stream\StreamInterface`.

#### getResource()

```php
StreamResourceInterface::getResource(): resource
```

Returns the underlying PHP stream resource.

---

#### close()

```php
StreamResourceInterface::close(): void
```

Closes the stream resource, making it unreadable or unwritable.

## ReadablePipe

`Icicle\Stream\Pipe\ReadablePipe` implements `Icicle\Stream\ReadableStreamInterface`, so it is interoperable with any other class implementing one of the stream interfaces.

When the other end of the connection is closed and a read is pending, that read will be fulfilled with an empty string. Subsequent reads will then reject with an instance of `Icicle\Stream\Exception\UnreadableException` and `isReadable()` will return `false`.

#### ReadablePipe Constructor

```php
$stream = new ReadablePipe(resource $resource)
```

Creates a readable stream from the given stream resource (note only stream resources created from pipes and sockets are supported, *not* file streams).

## WritablePipe

`Icicle\Stream\Pipe\WritablePipe` implements `Icicle\Stream\WritableStreamInterface`, so it is interoperable with any other class implementing one of the stream interfaces.

#### WritablePipe Constructor

```php
$stream = new WritablePipe(resource $resource)
```

Creates a writable stream from the given stream resource (note only stream resources created from pipes and sockets are supported, *not* file streams).

## DuplexPipe

`Icicle\Stream\Pipe\DuplexPipe` implements `Icicle\Stream\DuplexStreamInterface`, making it both a readable stream and a writable stream.

#### DuplexPipe Constructor

```php
$stream = new DuplexPipe(resource $resource)
```

Creates a duplex stream from the given stream resource (note only stream resources created from pipes and sockets are supported, *not* file streams).

## Functions

#### Stream\pipe()

```php
Icicle\Stream\pipe(
    ReadableStreamInterface $source
    WritableStreamInterface $dest,
    bool $end = true,
    int $length = 0,
    string|null $byte = null
    float $timeout = 0
): Generator
```

Returns a generator that should be used within a coroutine or used to create a new coroutine. Pipes all data read from this stream to the writable stream. If `$length` is not `0`, only `$length` bytes will be piped to the writable stream. The returned promise is fulfilled with the number of bytes piped once the writable stream is no longer writable, `$length` bytes have been piped, or `$byte` is encountered in the stream.

##### Parameters
`$source`
:   A readable stream to pipe data from.

`$dest`
:   A writable stream to pipe data to. All data from `$source` will be written to `$dest` as it becomes readable.

`$end`
:   Indicates if the destination stream should be closed when there is no more data in `$source`.

`$length`
:   The maximum number of bytes to pipe from `$source` to `$dest`.

`$byte`
:   If `$byte` is not `null`, piping will end once `$byte` is encountered in the stream.

`$timeout`
:   Number of seconds until the returned promise is rejected with a `TimeoutException` and the stream is closed if the data cannot be written to the stream. Use 0 for no timeout.

Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `int` | Fulfilled when the writable stream is no longer writable or when `$length` bytes have been piped or `$byte` is read from the stream.
Rejected | `Icicle\Stream\Exception\BusyError` | If a read was already pending on the stream.
Rejected | `Icicle\Stream\Exception\UnreadableException` | If the stream is no longer readable.
Rejected | `Icicle\Stream\Exception\ClosedException` | If the stream is unexpectedly closed.
Rejected | `Icicle\Promise\Exception\TimeoutException` | If reading from the stream times out.

---

#### Stream\readTo()

```php
Icicle\Stream\readTo(
    ReadableStreamInterface $source
    int $length,
    float $timeout = 0
): Generator
```

Returns a generator that should be used within a coroutine or used to create a new coroutine. Reads data from the given readable stream until the given number of bytes has been read from the stream.

##### Parameters
`$source`
:   A readable stream to read from.

`$length`
:   The maximum number of bytes to read from `$source`.

`$timeout`
:   Number of seconds until the returned promise is rejected with a `TimeoutException` and the stream is closed if the data cannot be written to the stream. Use 0 for no timeout.

Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `string` | Fulfilled when the given number of bytes is read from the stream.
Rejected | `Icicle\Stream\Exception\BusyError` | If a read was already pending on the stream.
Rejected | `Icicle\Stream\Exception\UnreadableException` | If the stream is no longer readable.
Rejected | `Icicle\Stream\Exception\ClosedException` | If the stream is unexpectedly closed.
Rejected | `Icicle\Promise\Exception\TimeoutException` | If reading from the stream times out.

---

#### Stream\readUntil()

```php
Icicle\Stream\readUntil(
    ReadableStreamInterface $source
    string $needle,
    int $maxlength = 0,
    float $timeout = 0
): Generator
```

Returns a generator that should be used within a coroutine or used to create a new coroutine. Reads data from the given readable stream until the given string of bytes is read from the stream or the max length is reached. The matched string of bytes is included in the result string.

##### Parameters
`$source`
:   A readable stream to read from.

`$needle`
:   The string to match against while reading.

`$maxLength`
:   The maximum number of bytes to read from `$source`.

`$timeout`
:   Number of seconds until the returned promise is rejected with a `TimeoutException` and the stream is closed if the data cannot be written to the stream. Use 0 for no timeout.

Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `string` | Fulfilled when the given string is read from the stream or the max length is reached.
Rejected | `Icicle\Stream\Exception\BusyError` | If a read was already pending on the stream.
Rejected | `Icicle\Stream\Exception\UnreadableException` | If the stream is no longer readable.
Rejected | `Icicle\Stream\Exception\ClosedException` | If the stream is unexpectedly closed.
Rejected | `Icicle\Promise\Exception\TimeoutException` | If reading from the stream times out.

---

#### Stream\readAll()

```php
Icicle\Stream\readAll(
    ReadableStreamInterface $source
    int $maxlength = 0,
    float $timeout = 0
): Generator
```

Returns a generator that should be used within a coroutine or used to create a new coroutine. Reads data from the given readable stream until stream is no longer readable or the max length is reached.

##### Parameters
`$source`
:   A readable stream to read from.

`$maxLength`
:   The maximum number of bytes to read from `$source`.

`$timeout`
:   Number of seconds until the returned promise is rejected with a `TimeoutException` and the stream is closed if the data cannot be written to the stream. Use 0 for no timeout.

Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `string` | Fulfilled when the stream is no longer readable or the max length is reached.
Rejected | `Icicle\Stream\Exception\BusyError` | If a read was already pending on the stream.
Rejected | `Icicle\Stream\Exception\UnreadableException` | If the stream is no longer readable.
Rejected | `Icicle\Stream\Exception\ClosedException` | If the stream is unexpectedly closed.
Rejected | `Icicle\Promise\Exception\TimeoutException` | If reading from the stream times out.

---

#### Stream\pair()

```php
Icicle\Stream\pair(): resource[]
```

Returns a pair of connected stream socket resources.

---

#### Stream\stdin()

```php
Icicle\Stream\stdin(): ReadableStreamInterface
```

Returns a global readable stream instance for STDIN.

---

#### Stream\stdout()

```php
Icicle\Stream\stdout(): WritableStreamInterface
```

Returns a global writable stream instance for STDOUT.

---

#### Stream\stderr()

```php
Icicle\Stream\stderr(): WritableStreamInterface
```

Returns a global writable stream instance for STDERR.
