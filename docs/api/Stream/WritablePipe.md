`Icicle\Stream\Pipe\WritablePipe` implements `Icicle\Stream\WritableStream`, so it is interoperable with any other class implementing one of the stream interfaces.

### WritablePipe Constructor

    $stream = new WritablePipe(resource $resource, bool $autoClose = true)

Creates a writable stream from the given stream resource (note only stream resources created from pipes and sockets are supported, *not* file streams).
