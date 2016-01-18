A readable and writable I/O stream.

**Implements**
:   [`Icicle\Stream\DuplexStream`](DuplexStream.md)

This class, along with [`ReadablePipe`](Pipe.ReadablePipe.md) and [`WritablePipe`](Pipe.WritablePipe.md), implement asynchronous streams over [native PHP stream resources](http://php.net/manual/en/book.stream.php). Many of Icicle's core stream types extend or use this class, as most are based on underlying stream resources.

!!! warning
    While this class should work without error on any valid PHP stream resource, only some types of streams are properly asynchronous, and *stream operations are only guaranteed to be non-blocking for certain types of streams*. As such, only stream resources created from pipes and sockets are supported, and *not* file streams.


## __construct()

    new DuplexPipe(resource $resource, bool $autoClose = true)

Creates a duplex stream from the given stream resource.
