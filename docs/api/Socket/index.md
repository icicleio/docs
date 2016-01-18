This optional package provides an asynchronous stream socket server, client, connector, and datagram. The package is available on [Packagist](https://packagist.org) as [`icicleio/socket`](https://packagist.org/packages/icicleio/socket).

!!! note
    To enable using SSL/TLS on sockets, you must install the [openssl extension](http://php.net/manual/en/book.openssl.php).

The socket component implements network sockets as coroutine-based streams, server, and datagram. Creating a server and accepting connections is very simple, requiring only a few lines of code.

The example below implements a simple HTTP server listening on 127.0.0.1:8080 that responds to any request with the contents of the client request as the body of the response. This example is implemented using coroutines (see the [Coroutine API documentation](../Coroutine/index.md)) and the basic sockets provided by the `icicleio/socket` package.

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


## connect()

    Icicle\Socket\connect(
        string $ip,
        int|null $port = null,
        array $options = []
    ): \Generator

Connects asynchronously to the given host on the given port. Uses the global connector interface that can be set using `Icicle\Socket\connector()`. See [`Connector::connect()`](Connector.Connector.md#connect) for more information.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.


## connector()

    Icicle\Socket\connector(
        Icicle\Socket\Connector\Connector|null $connector = null
    ): Icicle\Socket\Connector\Connector

Gets the global connector instance. If a connector instance is provided, that instance is set as the global connector instance.

### Parameters
`$connector`
:   A connector instance to use as the global socket connector.


## parseName()

    Icicle\Socket\parseName(string $name): array

Parses a name of the format `"ip:port"`, returning an array containing the IP address and port.

### Parameters
`string $name`
:   The name to parse.

### Return value
`[string, int|null]`
:   An array containing the IP address or unix socket path and port number or `null` if a unix socket.


## parseAddress()

    Icicle\Socket\parseAddress(string $address): string

Formats given address into a string. Converts integer to IPv4 address, wraps IPv6 address in brackets.

### Parameters
`string $address`
:   The address string to parse.

### Return value
`string`
:   Formatted IP address.


## makeName()

    Icicle\Socket\makeName(string $address, int|null $port = null): string

Creates string of format `"$address[:$port]"`.

### Parameters
`string $address`
:   Address or path.

`int|null $port = null`
:   Port number or `null` for unix socket.

### Return value
`string`
:   Formatted address and port.


## makeUri()

    Icicle\Socket\makeUri(string $protocol, string $address, int $port = null)

Creates string of format `"$protocol://$address[:$port]"`.

#### Parameters
`string $protocol`
:   URI protocol.

`string $address`
:   Address or path.

`int|null $port = null`
:   Port number or `null` for unix socket.

### Return value
`string`
:   Formatted URI.


## getName()

    Icicle\Socket\getName(resource $socket, bool $peer = true): array

Parses the IP address and port of a network socket. Calls stream_socket_get_name() and then parses the returned string. Returns the IP address and port pair.

### Parameters
`resoruce $socket`
:   Stream socket resource.

`bool $peer`
:   `true` for remote IP and port, `false` for local IP and port.

### Return value
`[string, int]`
:   Array containing the IP address and port number (returns `0` for the port number for unix sockets`).
