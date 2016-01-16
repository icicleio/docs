`Icicle\Stream\Pipe\ReadablePipe` implements `Icicle\Stream\ReadableStream`, so it is interoperable with any other class implementing one of the stream interfaces.

When the other end of the connection is closed and a read is pending, that read will be fulfilled with an empty string. Subsequent reads will then reject with an instance of `Icicle\Stream\Exception\UnreadableException` and `isReadable()` will return `false`.


### ReadablePipe Constructor

    $stream = new ReadablePipe(resource $resource, bool $autoClose = true)

Creates a readable stream from the given stream resource (note only stream resources created from pipes and sockets are supported, *not* file streams).
