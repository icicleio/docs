`Icicle\Stream\Pipe\DuplexPipe` implements `Icicle\Stream\DuplexStream`, making it both a readable stream and a writable stream.

### DuplexPipe Constructor

    $stream = new DuplexPipe(resource $resource, bool $autoClose = true)

Creates a duplex stream from the given stream resource (note only stream resources created from pipes and sockets are supported, *not* file streams).
