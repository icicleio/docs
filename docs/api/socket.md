This optional package provides an asynchronous stream socket server, client, connector, and datagram. The package is available on [Packagist](https://packagist.org) as [`icicleio/socket`](https://packagist.org/packages/icicleio/socket).

!!! note
    To enable using SSL/TLS on sockets, you must install the [openssl extension](http://php.net/manual/en/book.openssl.php).

The socket component implements network sockets as coroutine-based streams, server, and datagram. Creating a server and accepting connections is very simple, requiring only a few lines of code.

The example below implements a simple HTTP server listening on 127.0.0.1:8080 that responds to any request with the contents of the client request as the body of the response. This example is implemented using coroutines (see the [Coroutine API documentation](coroutine.md)) and the basic sockets provided by the `icicleio/socket` package.

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Loop;
use Icicle\Socket\Socket;
use Icicle\Socket\Server\Server;
use Icicle\Socket\Server\DefaultServerFactory;

$server = (new DefaultServerFactory())->create('127.0.0.1', 8080);

$generator = function (Server $server) {
    printf("Server listening on %s:%d\n", $server->getAddress(), $server->getPort());

    $generator = function (Socket $socket) {
        $request = '';
        do {
            $request .= (yield $socket->read(0, "\n"));
        } while (substr($request, -4) !== "\r\n\r\n");

        $message = sprintf("Received the following request:\r\n\r\n%s", $request);

        $data  = "HTTP/1.1 200 OK\r\n";
        $data .= "Content-Type: text/plain\r\n";
        $data .= sprintf("Content-Length: %d\r\n", strlen($message));
        $data .= "Connection: close\r\n";
        $data .= "\r\n";
        $data .= $message;

        yield $socket->write($data);

        $socket->close();
    };

    while ($server->isOpen()) {
        // Handle client in a separate coroutine so this coroutine is not blocked.
        $coroutine = new Coroutine($generator(yield $server->accept()));
        $coroutine->done(null, function (\Exception $exception) {
            printf("Client error: %s\n", $exception->getMessage());
        });
    }
};

$coroutine = new Coroutine($generator($server));
$coroutine->done();

Loop\run();
```


## Server\Server

The `\Icicle\Socket\Server\BasicServer` class implements `\Icicle\Socket\Server\Server`, a coroutine-based interface for creating a TCP server and accepting connections.

### Server Constructor

    $server = new BasicServer(resource $socket, bool $autoClose = true)

Creates a server from a stream socket server resource generated from `stream_socket_server()`. Generally it is better to use `\Icicle\Socket\Server\ServerFactory` to create a `\Icicle\Socket\Server\BasicServer` instance.

### accept()

    Server::accept(bool $autoClose = true): \Generator

A coroutine that is resolved with a `\Icicle\Socket\Socket` object when a connection is accepted.

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`bool $autoClose = true`
:   Use `true` to have the return `Socket` object close automatically on destruct, `false` to avoid automatic closure. Only in rare circumstances should this parameter be `false`.

#### Resolution value
`\Icicle\Socket\Socket`
:   Accepted client socket.

#### Rejection reasons
`\Icicle\Socket\Exception\BusyException`
:   If the server already had an accept pending.

`\Icicle\Socket\Exception\UnavailableException`
:   If the server was previously closed.

`\Icicle\Socket\Exception\ClosedException`
:   If the server is closed during pending accept.

### getAddress()

    Server::getAddress(): string

Returns the local IP address as a string.

### getPort()

    Server::getPort(): int

Returns the local port.


## Server\ServerFactory

`\Icicle\Socket\Server\DefaultServerFactory` (implements `\Icicle\Socket\Server\ServerFactory`) can be used to create server instances from a IP or unix socket path, port number (`null` for unix socket), and list of options.

### create()

```php
ServerFactory::create(
    string $host,
    int|null $port = null,
    mixed[] $options = []
): \Icicle\Socket\Server\Server
```

Creates a server bound and listening on the given ip or unix socket path and port number (`null` for unix socket).

#### Parameters
`string $host`
:   IP address or unix socket path.

`int|null $port = null`
:   Port number or null for unix socket.

`mixed[] $options = []`
:   An associative array of server socket options. The possible options are given below.

    Option | Type | Description
    :-- | :-- | :--
    `backlog` | `int` | Connection backlog size. Note that the operating system variable `SOMAXCONN` may set an upper limit and may need to be changed to allow a larger backlog size.
    `pem` | `string` | Path to PEM file containing certificate and private key to enable SSL on client connections.
    `passphrase` | `string` | PEM passphrase if applicable.
    `name` | `string` | Name to use as SNI identifier. If not set, name will be guessed based on `$host`.
    `verify_peer` | `bool` | True to verify client certificate. Normally should be false on the server.
    `allow_self_signed` | `bool` | Set to true to allow self-signed certificates. Defaults to false.
    `verify_depth` | `int` | Max levels of certificate authorities the verifier will transverse. Defaults to 10.


## Socket

`\Icicle\Socket\NetworkSocket` implements `\Icicle\Socket\Socket` and is used as the fulfillment value of the coroutine returned by `\Icicle\Socket\Server\Server::accept()` ([see documentation above](#accept)). (Note that `\Icicle\Socket\Server\BasicServer` can be easily extended and modified to fulfill accept requests with different objects implementing `\Icicle\Socket\Socket`.)

The class extends `\Icicle\Stream\Pipe\DuplexPipe`, so it inherits all the readable and writable stream methods as well as adding those below.

### BasicSocket Constructor

    $socket = new BasicSocket(resource $socket, bool $autoClose = true)

Creates a socket object from the given stream socket resource.

### enableCrypto()

    Socket::enableCrypto(int $method, float $timeout = 0): \Generator

Enables encryption on the socket. For Socket objects created from `\Icicle\Socket\Server\Server::accept()`, a PEM file must have been provided when creating the server socket (see `\Icicle\Socket\Server\ServerFactory`). Use the `STREAM_CRYPTO_METHOD_*_SERVER` constants when enabling crypto on remote clients (e.g., created by `\Icicle\Socket\Server\Server::accept()`) and the `STREAM_CRYPTO_METHOD_*_CLIENT` constants when enabling crypto on a local client connection (e.g., created by `\Icicle\Socket\Connector\Connector::connect()`).

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`int $method`
:   One of (or combination of) the server crypto flags, e.g. `STREAM_CRYPTO_METHOD_ANY_SERVER` for incoming (remote) clients, `STREAM_CRYPTO_METHOD_ANY_CLIENT` for outgoing (local) clients.

`float $timeout = 0`
:   Seconds to wait between reads/writes to enable crypto before failing. Use `0` for no timeout.

#### Rejection reasons
`\Icicle\Exception\Socket\FailureException`
:   If enabling crypto fails.

`\Icicle\Exception\Socket\UnreadableException`
:   If the socket is unreadable.

`\Icicle\Exception\Socket\UnwritableException`
:   If the socket is unwritable.

### getLocalAddress()

    Socket::getLocalAddress(): string

#### Return value
`string`
:   Returns the local IP address as a string.

### getLocalPort()

    Socket::getLocalPort(): int

#### Return value
`int`
:   Returns the local port.

### getRemoteAddress()

    Socket::getRemoteAddress(): string

#### Return value
`string`
:   Returns the remote IP address as a string.

---

### getRemotePort()

```php
Socket::getRemotePort(): int
```

Returns the remote port.


## Connector\Connector

The `\Icicle\Socket\Connector\DefaultConnector` class (implements `\Icicle\Socket\Connector\Connector`) asynchronously connects to a remote server, returning a coroutine that is fulfilled with an instance of `\Icicle\Socket\Socket` when the connection is successfully established.

!!! warning
    The *host should be given as an IP address*, as DNS lookups performed by PHP are synchronous (blocking). If you wish to use domain names instead of IPs, see [`\Icicle\Dns\Connector\Connector`](dns.md#connector).

### connect()

    Connector::connect(
        string $host,
        int|null $port = null,
        mixed[] $options = []
    ): \Generator

Connects asynchronously to the given IP or unix socket path on the given port number (`null` for unix socket).

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`string $host`
:   IP address or unix socket path. (Using a domain name will cause a blocking DNS resolution. Use the DNS component to perform non-blocking DNS resolution.)

`int|null $port = null`
:   Port number or `null` for unix socket.

`mixed[] $options = []`
:   An associative array of client socket options. The possible options are given below.

    Option | Type | Description
    :-- | :-- | :--
    `protocol` | `string` | The protocol to use, such as tcp, udp, s3, ssh. Defaults to tcp.
    `timeout` | `float` | Number of seconds until connection attempt times out. Defaults to 10 seconds.
    `name` | `string` | Name to verify certificate. May match CN or SAN names on certificate. (PHP 5.6+)
    `cn` | `string` | Host name (common name) used to verify certificate. e.g., `*.google.com`
    `allow_self_signed` | `bool` | Set to `true` to allow self-signed certificates. Defaults to `false`.
    `verify_depth` | `int` | Max levels of certificate authorities the verifier will transverse. Defaults to 10.
    `cafile` | `string` | Path to bundle of root certificates to verify against.

#### Resolution value
`\Icicle\Socket\Socket`
:   Fulfilled with once the connection is established.

#### Rejection reasons
`\Icicle\Socket\Exception\FailureException`
:   If the connection attempt fails (such as an invalid host).

`\Icicle\Awaitable\Exception\TimeoutException`
:   If the connection attempt times out.

!!! tip
    See <http://curl.haxx.se/docs/caextract.html> for links to download a bundle of CA Root Certificates that may be used for the cafile option if needed.


## Datagram\Datagram

The `\Icicle\Socket\Datagram\BasicDatagram` class implements `\Icicle\Socket\Datagram\Datagram`, a coroutine-based interface for creating a UDP listener and sender.

### BasicDatagram Constructor

    $datagram = new BasicDatagram(resource $socket, bool $autoClose = true)

Creates a datagram from a stream socket server resource generated from `stream_socket_server()`. Generally it is better to use `\Icicle\Socket\Datagram\DefaultDatagramFactory` to create a `\Icicle\Socket\Datagram\Datagram` instance.

### receive()

    Datagram::receive(int $length, float $timeout): \Generator

A coroutine that is fulfilled with an array when a data is received on the UDP socket (datagram). The array is a 0-indexed array containing the IP address, port, and data received, in that order.

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`int $length = 0`
:   The maximum number of bytes to receive. Use `0` for an unlimited amount.

`float $timeout = 0`
:   Number of seconds until the returned awaitable is rejected with a `TimeoutException` and the stream is closed if the data cannot be written to the stream. Use `0` for no timeout.

#### Resolution value
`[string, int, string]`
:   An array with three elements: The IP address of the data sender, the port of the data sender, and the data.

### send()

    Datagram::send(
        string $address,
        int $port,
        string $data
    ): \Generator

Send the given data to the IP address and port. This coroutine is fulfilled with the amount of data sent once the data has successfully been sent.

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`string $address`
:   IP address of receiver.

`int $port`
:   Port of receiver.

`string $data`
:   Data to send.

#### Resolution value
`int`
:   Length of sent data.

### getAddress()

    Datagram::getAddress(): string

#### Return value
`string`
:   Returns the local IP address as a string.

### getPort()

    Datagram::getPort(): int

#### Return value
`int`
:   Returns the local port.


## Datagram\DatagramFactory

`\Icicle\Socket\Datagram\DatagramFactory` (implements `\Icicle\Socket\Datagram\DatagramFactory`) can be used to create datagram instances from a hostname or unix socket path, port number (`null` for unix socket), and list of options.

### create()

    DatagramFactory::create(
        string $host,
        int $port,
        mixed[] $options = []
    ): \Icicle\Socket\Datagram\Datagram

Creates a datagram bound and listening on the given IP and port number. No options are defined in this implementation.

#### Parameters
`string $host`
:   IP address of receiver.

`int $port`
:   Port of receiver.

`mixed[] $options = []`
:   Array of additional options.


## Functions

### connect()

    \Icicle\Socket\connect(
        string $ip,
        int|null $port = null,
        array $options = []
    ): \Generator

Connects asynchronously to the given host on the given port. Uses the global connector interface that can be set using `\Icicle\Socket\connector()`. See [connect()](#connect) above for more information.

!!! note
    **Coroutine**: Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### connector()

    \Icicle\Socket\connector(
        \Icicle\Socket\Connector\Connector|null $connector = null
    ): \Icicle\Socket\Connector\Connector

Gets the global connector instance. If a connector instance is provided, that instance is set as the global connector instance.

#### Parameters
`$connector`
:   A connector instance to use as the global socket connector.

#### parseName()

    \Icicle\Socket\parseName(string $name): array

Parses a name of the format `"ip:port"`, returning an array containing the IP address and port.

#### Parameters
`string $name`
:   The name to parse.

#### Return value
`[string, int|null]`
:   An array containing the IP address or unix socket path and port number or `null` if a unix socket.

### Socket\parseAddress()

    \Icicle\Socket\parseAddress(string $address): string

Formats given address into a string. Converts integer to IPv4 address, wraps IPv6 address in brackets.

#### Parameters
`string $address`
:   The address string to parse.

#### Return value
`string`
:   Formatted IP address.

### Socket\makeName()

    \Icicle\Socket\makeName(string $address, int|null $port = null): string

Creates string of format `"$address[:$port]"`.

#### Parameters
`string $address`
:   Address or path.

`int|null $port = null`
:   Port number or `null` for unix socket.

#### Return value
`string`
:   Formatted address and port.

### Socket\makeUri()

    \Icicle\Socket\makeUri(string $protocol, string $address, int $port = null)

Creates string of format `"$protocol://$address[:$port]"`.

##### Parameters
`string $protocol`
:   URI protocol.

`string $address`
:   Address or path.

`int|null $port = null`
:   Port number or `null` for unix socket.

#### Return value
`string`
:   Formatted URI.

### Socket\getName()

    \Icicle\Socket\getName(resource $socket, bool $peer = true): array

Parses the IP address and port of a network socket. Calls stream_socket_get_name() and then parses the returned string. Returns the IP address and port pair.

#### Parameters
`resoruce $socket`
:   Stream socket resource.

`bool $peer`
:   `true` for remote IP and port, `false` for local IP and port.

#### Return value
`[string, int]`
:   Array containing the IP address and port number (returns `0` for the port number for unix sockets`).
