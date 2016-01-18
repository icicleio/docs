An in-memory, writable, seekable sink.

**Implements**
:   [`Icicle\Stream\DuplexStream`](DuplexStream.md)
:   [`Icicle\Stream\SeekableStream`](SeekableStream.md)

`Icicle\Stream\MemorySink` acts as a buffered sink with a seekable read/write pointer. All data written to the sink remains in the sink. The read/write pointer may be moved anywhere within the buffered sink using `seek()`. The current position of the pointer may be determined with `tell()`. Since all data remains in the sink, the entire length of the sink is available with `getLength()`.


### Example

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
