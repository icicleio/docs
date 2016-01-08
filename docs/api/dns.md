This optional package provides an asynchronous DNS query executor, resolver, and client connector. The package is available on [Packagist](https://packagist.org) as [`icicleio/dns`](https://packagist.org/packages/icicleio/dns).

This package provides a set of global [functions](#functions) in the `Icicle\Dns` namespace to perform asynchronous DNS queries that should be sufficient for most applications. However, if desired an application may customize the methods used to perform DNS queries using the objects described below.


## Executor\Executor

Executors are the foundation of the DNS component, performing any DNS query and returning the full results of that query. Resolvers and connectors depend on executors to perform the DNS query required for their operation.

### execute()

    Executor::execute(
        string $domain,
        string|int $type,
        mixed[] $options = []
    ): \Generator

Executes a DNS query.

An executor will retry a query a number of times if it doesn't receive a response within `timeout` seconds. The number of times a query will be retried before failing is defined by `retries`, with `timeout` seconds elapsing between each query attempt.

!!! note
    [**Coroutine**](../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`string $name`
:   Domain name.

`string|int $type`
:   Query type (e.g., `'A'`, `'MX'`, `'AAAA'`, `'NS'`).

`mixed[] $options = []`
:   An array of additional options.

    Option | Type | Description
    :-- | :-- | :--
    `timeout` | `float` | Timeout until query fails. Default is 2 seconds.
    `retries` | `int` | Number of times to attempt the query before failing. Default is 5 times.

#### Resolution value
`LibDNS\Messages\Message`
:   Response message.

#### Rejection reasons
`Icicle\Dns\Exception\FailureException`
:   If sending the request or parsing the response fails.

`Icicle\Dns\Exception\MessageException`
:   If the server returns a non-zero response code or no response is received from the server.


## Executor\BasicExecutor

The default executor implementation that implements [`Icicle\Dns\Executor\Executor`](#executorexecutor).

### __construct()

    BasicExecutor::__construct(
        string $address,
        int $port = Executor::DEFAULT_PORT,
        Icicle\Socket\Connector\Connector $connector = null
    )

Constructs a new DNS executor.

#### Parameters
`string $address`
:   The IP address of the DNS resolver to use.

`int $port = 53`
:   The port to connect to the DNS server. Defaults to `Executor::DEFAULT_PORT` which has the value `53`, the standard port for the DNS protocol.

`Icicle\Socket\Connector\Connector $connector = null`
:   A socket connector instance to use to connect to the DNS server socket. If left as `null`, the [default socket connector](socket.md#socketconnector) is used.


### getAddress()

    Executor::getAddress(): string

Gets the IP address of the DNS server used by this executor.

#### Return value
`string`
:   The server IP address.


### getPort()

    Executor::getPort(): int

Gets the port of the DNS server used by this executor.

#### Return value
`int`
:   The server port.



## Executor\MultiExecutor

Implements [`Icicle\Dns\Executor\Executor`](#executorexecutor). Combines multiple executors to send queries to several name servers so queries can be resolved even if some name servers stop responding. Subsequent queries are initially sent to the last server that successfully responded to a query.

#### Example:

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Dns\Executor\Executor;
use Icicle\Dns\Executor\MultiExecutor;
use Icicle\Loop;
use LibDNS\Messages\Message;

$executor = new MultiExecutor();

$executor->add(new Executor('8.8.8.8'));
$executor->add(new Executor('8.8.4.4'));

// Executor will send query to 8.8.4.4 if 8.8.8.8 does not respond.
$coroutine = new Coroutine($executor->execute('google.com', 'MX'));

$coroutine->done(
    function (Message $message) {
        foreach ($message->getAnswerRecords() as $resource) {
            echo "TTL: {$resource->getTTL()} Value: {$resource->getData()}\n";
        }
    },
    function (Exception $exception) {
        echo "Query failed: {$exception->getMessage()}\n";
    }
);

Loop\run();
```

### add()

    MultiExecutor::add(Executor $executor)

Adds an executor to the multi-executor.

#### Parameters
`Icicle\Dns\Executor\Executor $executor`
:   An executor instance to add to the multi-executor.


## Resolver\Resolver

A resolver finds the IP addresses for a given domain. A resolver is essentially a specialized executor that performs only `A` or `AAAA` queries (defaults to `A` queries, use `'mode' => Resolver::IPv6` in the `$options` array for `AAAA` records).

The default implementation is `Icicle\Dns\Resolver\BasicResolver`, which is constructed by passing an `Icicle\Dns\Executor\Executor` instance to the constructor that is used to execute queries to resolve domains. If no executor is given, the global executor instance is used.

### resolve()

    Resolver::resolve(
        string $domain,
        array $options = []
    ): \Generator

Resolves a given domain and yields an array of IP addresses that match the given domain.

!!! note
    [**Coroutine**](../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`string $domain`
:   The domain name to resolve.

`mixed[] $options`
:   An associative array of additional options. The available options are as follows:

    Option | Type | Description
    :-- | :-- | :--
    `mode` | `int` | Resolution mode: IPv4 or IPv6. Use the constants `Resolver::IPv4` or `Resolver::IPv6`.
    `timeout` | `float` | Timeout until query fails. Default is 2 seconds.
    `retries` | `int` | Number of times to attempt the query before failing. Default is 5 times.

Like executors, a resolver will retry a query `retries` times if the name server does not respond within `timeout` seconds.

#### Resolution value
`string[]`
:   Array of resolved IP addresses. May be empty if the query is successful but no IP addresses could be found.

#### Rejection reasons
`Icicle\Dns\Exception\FailureException`
:  If sending the request or parsing the response fails.

`Icicle\Dns\Exception\MessageException`:
:   If the server returns a non-zero response code or no response is received.

!!! note
    Even if there is only one or no matches at all for the given domain name, the return value will still resolve with an array if the DNS query itself was successful.

#### Example

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Dns\Resolver\BasicResolver;
use Icicle\Loop;

$resolver = new BasicResolver();

$coroutine = new Coroutine($resolver->resolve('google.com'));

$coroutine->done(
    function (array $ips) {
        foreach ($ips as $ip) {
            echo "IP: {$ip}\n";
        }
    },
    function (\Exception $exception) {
        echo "Query failed: {$exception->getMessage()}\n";
    }
);

Loop\run();
```


## Resolver\BasicResolver

The default resolver implementation that implements [`Icicle\Dns\Resolver\Resolver`](#resolverresolver).

### __construct()

    BasicResolver::__construct(
        Icicle\Dns\Executor\Executor|null $executor = null
    )

Constructs a new DNS resolver.

#### Parameters
`Icicle\Dns\Executor\Executor $executor = null`
:   Executor object to perform DNS queries. If none is provided, the default global executor will be used.


## Connector\Connector

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
    [**Coroutine**](../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

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

    Additionally, all the [other options available](socket.md#connect) to `Icicle\Socket\Connector\Connector::connect()` may also be used.

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


## Functions

Since most applications don't need specialized DNS executors, several functions are provided that allow you to work with a global DNS executor instance.

### execute()

    Icicle\Dns\execute(
        string $name,
        string|int $type,
        mixed[] $options = []
    ): \Generator

Uses the global executor to execute a DNS query.

See [`Executor::execute()`](#execute) for details on how to call the execute function.


### executor()

Accesses and sets the global executor instance.

    Icicle\Dns\executor(
        Icicle\Dns\Executor\Executor|null $executor = null
    ): Icicle\Dns\Executor\Executor

#### Parameters
`Icicle\Dns\Executor\Executor|null $executor`
:   The executor to set, as the global instance, or `null` to use the current instance.

#### Return value
`Icicle\Dns\Executor\Executor`
:   The global executor instance.


### resolve()

    Icicle\Dns\resolve(
        string $domain,
        mixed[] $options = []
    ): \Generator

Uses the global resolver to resolve the IP address of a domain name.

See [`Resolver::resolve()`](#resolve) for details on how to call the resolve function.


### resolver()

    Icicle\Dns\resolver(
        Icicle\Dns\Resolver\Resolver|null $resolver = null
    ): Icicle\Dns\Resolver\Resolver

Accesses and sets the global resolver instance.

#### Parameters
`Icicle\Dns\Resolver\Resolver|null $resolver = null`
:   The resolver to set, as the global instance, or `null` to use the current instance.

#### Return value
`Icicle\Dns\Resolver\Resolver`
:   The global resolver instance.


### connect()

    Icicle\Dns\connect(
        string $domain,
        int $port,
        mixed[] $options = []
    ): \Generator

Uses the global connector to connect to the domain on the given port.

See [`Connector::connect()`](#connect) for details on how to call the connect function.


### connector()

    Icicle\Dns\connector(
        Icicle\Dns\Connector\Connector|null $connector = null
    ): Icicle\Dns\Connector\Connector

Accesses and sets the global connector instance.

#### Parameters
`Icicle\Dns\Connector\Connector|null $connector = null`
:   The connector to set, as the global instance, or `null` to use the current instance.

#### Return value
`Icicle\Dns\Connector\Connector`
:   The global connector instance.
