The `Icicle\Socket\Server\BasicServer` class implements `Icicle\Socket\Server\Server`, a coroutine-based interface for creating a TCP server and accepting connections.

### Server Constructor

    $server = new BasicServer(resource $socket, bool $autoClose = true)

Creates a server from a stream socket server resource generated from `stream_socket_server()`. Generally it is better to use `Icicle\Socket\Server\ServerFactory` to create a `Icicle\Socket\Server\BasicServer` instance.

### accept()

    Server::accept(bool $autoClose = true): \Generator

A coroutine that is resolved with a `Icicle\Socket\Socket` object when a connection is accepted.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`bool $autoClose = true`
:   Use `true` to have the return `Socket` object close automatically on destruct, `false` to avoid automatic closure. Only in rare circumstances should this parameter be `false`.

#### Resolution value
`Icicle\Socket\Socket`
:   Accepted client socket.

#### Rejection reasons
`Icicle\Socket\Exception\BusyException`
:   If the server already had an accept pending.

`Icicle\Socket\Exception\UnavailableException`
:   If the server was previously closed.

`Icicle\Socket\Exception\ClosedException`
:   If the server is closed during pending accept.

### getAddress()

    Server::getAddress(): string

Returns the local IP address as a string.

### getPort()

    Server::getPort(): int

Returns the local port.
