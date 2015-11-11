This optional package provides an asynchronous DNS query executor, resolver, and client connector. The package is available on [Packagist](https://packagist.org) as [`icicleio/dns`](https://packagist.org/packages/icicleio/dns).



## Executor\ExecutorInterface

Executors are the foundation of the DNS component, performing any DNS query and returning the full results of that query. Resolvers and connectors depend on executors to perform the DNS query required for their operation.

The default implementation is `Icicle\Dns\Executor\Executor`.

### execute()

    ExecutorInterface::execute(
        string $domain,
        string|int $type,
        array $options = []
    ): Generator

Executes a DNS query.

An executor will retry a query a number of times if it doesn't receive a response within `timeout` seconds. The number of times a query will be retried before failing is defined by `retries`, with `timeout` seconds elapsing between each query attempt.

#### Parameters
`$name`
:   Domain name.

`$type`
:   Query type (e.g., `'A'`, `'MX'`, `'AAAA'`, `'NS'`).

`$options`
:   An array of additional options.

    Option | Type | Description
    :-- | :-- | :--
    `timeout` | `float` | Timeout until query fails. Default is 2 seconds.
    `retries` | `int` | Number of times to attempt the query before failing. Default is 5 times.

#### Return value
Generator that resolves in one of the following ways.

Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `LibDNS\Messages\Message` | Response message.
Rejected | `Icicle\Dns\Exception\FailureException` | If sending the request or parsing the response fails.
Rejected | `Icicle\Dns\Exception\MessageException` | If the server returns a non-zero response code or no response is received from the server.



## Executor\Executor

The default executor implementation that implements [`ExecutorInterface`](#executorexecutorinterface).

### __construct()

    Executor::__construct(
        string $address,
        int $port = Executor::DEFAULT_PORT,
        Icicle\Socket\Connector\ConnectorInterface $connector = null
    )

Constructs a new DNS executor.

#### Parameters
`$address`
:   The IP address of the DNS resolver to use.

`$port`
:   The port to connect to the DNS server. Defaults to `Executor::DEFAULT_PORT` which has the value `53`, the standard port for the DNS protocol.

`$connector`
:   A socket connector instance to use to connect to the DNS server socket. If left as `null`, the [default socket connector](socket.md#socketconnector) is used.


### getAddress()

    Executor::getAddress(): string

Gets the IP address of the DNS server used by this executor.

#### Return value
The server IP address.


### getPort()

    Executor::getPort(): int

Gets the port of the DNS server used by this executor.

#### Return value
The server port.



## Executor\MultiExecutor

Combines multiple executors to send queries to several name servers so queries can be resolved even if some name servers stop responding.

Implements [`ExecutorInterface`](#executorexecutorinterface).

### add()

    MultiExecutor::add(ExecutorInterface $executor)

Adds an executor to the multi-executor.

#### Parameters
`$executor`
:   An executor instance to add to the mulit-executor.



## Resolver\ResolverInterface

A resolver finds the IP addresses for a given domain. A resolver is essentially a specialized executor that performs only `'A'` queries.

The default implementation is `Icicle\Dns\Resolver\Resolver`, which is constructed by passing an `Icicle\Executor\ExecutorInterface` instance to the constructor that is used to execute queries to resolve domains. If no executor is given, one will be created by default, using `8.8.8.8` and `8.8.4.4` as DNS servers for the executor.

### resolve()

    ResolverInterface::resolve(
        string $domain,
        array $options = []
    ): Generator

Resolves a given domain and yields an array of IP addresses that match the given domain.

#### Parameters
`$domain`
:   The domain name to resolve.

`$options`
:   An associative array of additional options. The available options are as follows:

    Option | Type | Description
    :-- | :-- | :--
    `mode` | `int` | Resolution mode: IPv4 or IPv6. Use the constants `ResolverInterface::IPv4` or `ResolverInterface::IPv6`.
    `timeout` | `float` | Timeout until query fails. Default is 2 seconds.
    `retries` | `int` | Number of times to attempt the query before failing. Default is 5 times.

Like executors, a resolver will retry a query `retries` times if the name server does not respond within `timeout` seconds.

#### Return value
Generator that resolves in one of the following ways.

Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `array` | Array of resolved IP addresses. May be empty.
Rejected | `Icicle\Dns\Exception\FailureException` | If sending the request or parsing the response fails.
Rejected | `Icicle\Dns\Exception\MessageException` | If the server returns a non-zero response code or no response is received.

!!! note
    Even if there is only one or no matches at all for the given domain name, the return value will still resolve with an array if the DNS query itself was successful.

#### Example

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Dns\Executor\Executor;
use Icicle\Dns\Resolver\Resolver;
use Icicle\Loop;

$resolver = new Resolver();

$coroutine = new Coroutine($resolver->resolve('google.com'));

$coroutine->done(
    function (array $ips) {
        foreach ($ips as $ip) {
            echo "IP: {$ip}\n";
        }
    },
    function (Exception $exception) {
        echo "Query failed: {$exception->getMessage()}\n";
    }
);

Loop\run();
```



## Connector\ConnectorInterface

The connector component connects to a server by first resolving the hostname provided, then making the connection and resolving the returned coroutine with an instance of `Icicle\Socket\Client\ClientInterface`. `Icicle\Dns\Connector\Connector` implements `Icicle\Socket\Client\ConnectorInterface` and `Icicle\Dns\Connector\ConnectorInterface`, allowing it to be used anywhere a standard connector (`Icicle\Socket\Client\ConnectorInterface`) is required, or allowing components to require a resolving connector (`Icicle\Dns\Connector\ConnectorInterface`).

`Icicle\Dns\Connector\ConnectorInterface` defines a single method, `connect()` that should resolve a host name and connect to one of the resolved servers, resolving the coroutine with the connected client.

### connect()

    ConnectorInterface::connect(
        string $domain,
        int $port,
        array $options = [],
    ): Generator

`Icicle\Dns\Connector\Connector` will attempt to connect to one of the IP addresses found for a given host name. If the server at that IP is unresponsive, the connector will attempt to establish a connection to the next IP in the list until a server accepts the connection. Only if the connector is unable to connect to all of the IPs will it reject the coroutine returned from `connect()`. The constructor also optionally accepts an instance of `Icicle\Socket\Client\ConnectorInterface` if custom behavior is desired when connecting to the resolved host.

#### Parameters
`$domain`
:   The domain name to connect to.

`$port`
:   The socket port to connect to.

`$options`
:   An associative array of additional options. The available options are as follows:

    Option | Type | Description
    :-- | :-- | :--
    `mode` | `int` | Resolution mode: IPv4 or IPv6. Use the constants `ResolverInterface::IPv4` or `ResolverInterface::IPv6`.
    `timeout` | `float` | Timeout until query fails. Default is 2 seconds.
    `retries` | `int` | Number of times to attempt the query before failing. Default is 5 times.

    Additionally, all the [other options available](socket.md#connect) to `Icicle\Socket\Client\Connector::connect()` may also be used.

#### Return value
Resolution | Type | Description
:-: | :-- | :--
Fulfilled | `Icicle\Socket\Client\ClientInterface` | Connected client.
Rejected | `Icicle\Socket\Exception\FailureException` | If resolving the IP or connecting fails.

#### Example

```php
use Icicle\Dns\Connector\Connector;
use Icicle\Dns\Executor\Executor;
use Icicle\Dns\Resolver\Resolver;
use Icicle\Loop;
use Icicle\Socket\Client\ClientInterface;

$connector = new Connector();

$coroutine = new Coroutine($connector->connect('google.com', 80));

$coroutine->done(
    function (ClientInterface $client) {
        echo "IP: {$client->getRemoteAddress()}\n";
        echo "Port: {$client->getRemotePort()}\n";
    },
    function (Exception $exception) {
        echo "Connecting failed: {$exception->getMessage()}\n";
    }
);

Loop\run();
```



## Functions

Since most applications don't need specialized DNS executors, several functions are provided that allow you to work with a global DNS executor instance.

### execute()

    Dns\execute(
        string $name,
        string|int $type,
        array $options = []
    ): Generator

Uses the global executor to execute a DNS query.

See [`ExecutorInterface::execute()`](#execute) for details on how to call the execute function.


### executor()

Accesses and sets the global executor instance.

    Dns\executor(
        ExecutorInterface $executor = null
    ): ExecutorInterface

#### Parameters
`$executor`
:   The executor to set, as the global instance, or `null` to use the current instance.

#### Return value
The global executor instance.


### resolve()

    Dns\resolve(
        string $domain,
        array $options = []
    ): Generator

Uses the global resolver to resolve the IP address of a domain name.

See [`ResolverInterface::resolve()`](#resolve) for details on how to call the resolve function.


### resolver()

    Dns\resolver(
        ResolverInterface $resolver = null
    ): ResolverInterface

Accesses and sets the global resolver instance.

#### Parameters
`$resolver`
:   The resolver to set, as the global instance, or `null` to use the current instance.

#### Return value
The global resolver instance.


### connect()

    Dns\connect(
        string $domain,
        int $port,
        array $options = []
    ): Generator

Uses the global connector to connect to the domain on the given port.

See [`ConnectorInterface::connect()`](#connect) for details on how to call the connect function.


### connector()

    Dns\connector(
        ConnectorInterface $connector = null
    ): ConnectorInterface

Accesses and sets the global connector instance.

#### Parameters
`$connector`
:   The connector to set, as the global instance, or `null` to use the current instance.

#### Return value
The global connector instance.
