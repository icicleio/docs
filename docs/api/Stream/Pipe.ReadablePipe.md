A readable I/O stream.

**Implements**
:   [`Icicle\Stream\ReadableStream`](ReadableStream.md)

This class, along with [`DuplexPipe`](Pipe.DuplexPipe.md) and [`WritablePipe`](Pipe.WritablePipe.md), implement asynchronous streams over [native PHP stream resources](http://php.net/manual/en/book.stream.php). Many of Icicle's core stream types extend or use this class, as most are based on underlying stream resources.

!!! warning
    While this class should work without error on any valid PHP stream resource, only some types of streams are properly asynchronous, and *stream operations are only guaranteed to be non-blocking for certain types of streams*. As such, only stream resources created from pipes and sockets are supported, and *not* file streams.

When the other end of the connection is closed and a read is pending, that read will be fulfilled with an empty string. Subsequent reads will then reject with an instance of `Icicle\Stream\Exception\UnreadableException` and `isReadable()` will return `false`.


## __construct()

    new ReadablePipe(resource $resource, bool $autoClose = true)

Creates a readable stream from the given stream resource.
