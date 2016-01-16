The connector component connects to a server by first resolving the hostname provided, then making the connection and resolving the returned coroutine with an instance of `Icicle\Socket\Socket`. `Icicle\Dns\Connector\Connector` implements `Icicle\Socket\Connector\Connector` and `Icicle\Dns\Connector\Connector`, allowing it to be used anywhere a standard connector (`Icicle\Socket\Connector\Connector`) is required or allowing components to require a resolving connector (`Icicle\Dns\Connector\Connector`).

`Icicle\Dns\Connector\Connector` defines a single method, `connect()` that should resolve a host name and connect to one of the resolved servers, resolving the coroutine with the connected client.

### connect()

    Connector::connect(
        string $domain,
        int $port,
        array $options = [],
    ): \Generator

`Icicle\Dns\Connector\DefaultConnector` will attempt to connect to one of the IP addresses found for a given host name. If the server at that IP is unresponsive, the connector will attempt to establish a connection to the next IP in the list until a server accepts the connection. Only if the connector is unable to connect to all of the IPs will it reject the coroutine returned from `connect()`. The constructor also optionally accepts an instance of `Icicle\Socket\Connector\Connector` if custom behavior is desired when connecting to the resolved host.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`string $domain`
:   The domain name to connect to.

`int $port`
:   The socket port to connect to.

`mixed[] $options = []`
:   An associative array of additional options. The available options are as follows:

    Option | Type | Description
    :-- | :-- | :--
    `mode` | `int` | Resolution mode: IPv4 or IPv6. Use the constants `Resolver::IPv4` or `Resolver::IPv6`.
    `timeout` | `float` | Timeout until query fails. Default is 2 seconds.
    `retries` | `int` | Number of times to attempt the query before failing. Default is 5 times.

    Additionally, all the [other options available](../Socket/Connector.Connector.md#connect) to `Icicle\Socket\Connector\Connector::connect()` may also be used.

#### Resolution value
`Icicle\Socket\Socket`
:   Connected client socket object.

#### Rejection reasons
`Icicle\Socket\Exception\FailureException`
:   If resolving the IP or connecting fails.


#### Example

```php
use Icicle\Dns\Connector\DefaultConnector;
use Icicle\Loop;
use Icicle\Socket\Socket;

$connector = new DefaultConnector();

$coroutine = new Coroutine($connector->connect('google.com', 80));

$coroutine->done(
    function (Socket $client) {
        echo "IP: {$client->getRemoteAddress()}\n";
        echo "Port: {$client->getRemotePort()}\n";
    },
    function (Exception $exception) {
        echo "Connecting failed: {$exception->getMessage()}\n";
    }
);

Loop\run();
```
