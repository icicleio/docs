Interface for open network socket connections.

**Extends**
:   [`Icicle\Stream\DuplexStream`](../Stream/DuplexStream.md)
:   [`Icicle\Stream\Resource`](../Stream/Resource.md)

Provides methods for standard operations available on sockets. All socket connection types in Icicle implement this socket interface, including both TCP and UDP socket types. A standard, full featured implementation is provided as [`NetworkSocket`](NetworkSocket.md).

This interface extends [`DuplexStream`](../Stream/DuplexStream.md), so it inherits all the readable and writable stream methods as well as adding those specified below.


## enableCrypto()

    Socket::enableCrypto(int $method, float $timeout = 0): \Generator

Enables encryption on the socket. For Socket objects created from `Icicle\Socket\Server\Server::accept()`, a PEM file must have been provided when creating the server socket (see `Icicle\Socket\Server\ServerFactory`). Use the `STREAM_CRYPTO_METHOD_*_SERVER` constants when enabling crypto on remote clients (e.g., created by `Icicle\Socket\Server\Server::accept()`) and the `STREAM_CRYPTO_METHOD_*_CLIENT` constants when enabling crypto on a local client connection (e.g., created by `Icicle\Socket\Connector\Connector::connect()`).

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`int $method`
:   One of (or combination of) the server crypto flags, e.g. `STREAM_CRYPTO_METHOD_ANY_SERVER` for incoming (remote) clients, `STREAM_CRYPTO_METHOD_ANY_CLIENT` for outgoing (local) clients.

`float $timeout = 0`
:   Seconds to wait between reads/writes to enable crypto before failing. Use `0` for no timeout.

### Rejection reasons
`Icicle\Exception\Socket\FailureException`
:   If enabling crypto fails.

`Icicle\Exception\Socket\UnreadableException`
:   If the socket is unreadable.

`Icicle\Exception\Socket\UnwritableException`
:   If the socket is unwritable.


## getLocalAddress()

    Socket::getLocalAddress(): string

### Return value
`string`
:   Returns the local IP address as a string.


## getLocalPort()

    Socket::getLocalPort(): int

### Return value
`int`
:   Returns the local port.


## getRemoteAddress()

    Socket::getRemoteAddress(): string

### Return value
`string`
:   Returns the remote IP address as a string.


## getRemotePort()

```php
Socket::getRemotePort(): int
```

Returns the remote port.
