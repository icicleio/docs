Basic TCP server implementation.

**Implements**
:   [`Server\Server`](Server.Server.md)

!!! tip
    `BasicServer` can be easily extended and modified to fulfill accept requests with different objects implementing `Icicle\Socket\Socket`.


## __construct()

    new BasicServer(resource $socket, bool $autoClose = true)

Creates a server from a stream socket server resource generated from `stream_socket_server()`. Generally it is better to use `Icicle\Socket\Server\ServerFactory` to create a `Icicle\Socket\Server\BasicServer` instance.
