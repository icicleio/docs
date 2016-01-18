Basic datagram implementation.

**Implements**
:   [`Datagram\Datagram`](Datagram.Datagram.md)


## __construct()

    new BasicDatagram(resource $socket, bool $autoClose = true)

Creates a datagram from a stream socket server resource generated from `stream_socket_server()`. Generally it is better to use `Icicle\Socket\Datagram\DefaultDatagramFactory` to create a `Icicle\Socket\Datagram\Datagram` instance.
