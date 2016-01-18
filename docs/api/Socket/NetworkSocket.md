Primary socket implementation.

**Extends**
:   [`Icicle\Stream\Pipe\DuplexPipe`](../Stream/Pipe.DuplexPipe.md)

**Implements**
:   [`Icicle\Socket\Socket`](Socket.md)


## __construct()

    new NetworkSocket(resource $socket, bool $autoClose = true)

Creates a socket object from the given stream socket resource.

### Parameters
`$socket`
:   Stream socket resource.

`$autoClose`
:   True to close the resource on destruct, false to leave it open.
