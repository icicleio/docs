An in-memory stream.

**Implements**
:   [`Icicle\Stream\DuplexStream`](DuplexStream.md)

`Icicle\Stream\MemoryStream` objects act as a stream buffer, allowing consumers to be notified when data is available in the buffer. This class by itself is not particularly useful, but it can be extended to add functionality upon reading or writing, as well as acting as an example of how stream classes can be implemented.

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
