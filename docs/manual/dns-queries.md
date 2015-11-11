When connecting sockets, we normally must specify the IP address of the computer to connect to. What if we want to connect to a server by its domain name? To look up the IP address of a server by its domain name, we must perform a *DNS query*. We can use DNS queries to *resolve* a domain name and find the corresponding IP address so we can connect to it.

PHP already provides means of resolving domain names (using the [`gethostbyname()`](http://php.net/gethostbyname) function for example), but those functions are synchronous and use blocking I/O. Sometimes DNS queries can cause a noticeable delay. To solve this problem, Icicle provides asynchronous DNS executors so our code doesn't block when connecting through a domain name.



#### Example

The example below uses a resolver to asynchronously find the IP address for the domain `icicle.io`.

```php
use Icicle\Coroutine;
use Icicle\Dns\Resolver\Resolver;
use Icicle\Loop;

Coroutine\create(function () {
    $resolver = new Resolver();

    try {
        $ips = (yield $resolver->resolve('icicle.io'));

        foreach ($ips as $ip) {
            echo "IP: {$ip}\n";
        }
    } catch (Exception $exception) {
        echo "Error when executing query: {$exception->getMessage()}\n";
    }
})->done();

Loop\run();
```

Icicle uses [LibDNS](//github.com/DaveRandom/LibDNS) to create and parse DNS messages. Unfortunately the documentation for this library is currently limited to comments in the source code. If only using resolvers and connectors in this library, there is no need to worry about how LibDNS works. Executors returns coroutines that are resolved with `LibDNS\Messages\Message` instances, representing the response from the DNS server. Using these objects is simple and will be described in the executor section below.



## Executors

Executors are the foundation of the DNS component, performing any DNS query and returning the full results of that query. Resolvers and connectors depend on executors to perform the DNS query required for their operation.

Each executor implements `Icicle\Dns\Executor\ExecutorInterface` that defines a single method, `execute()`.

### Creating an Executor

The simplest executor is `Icicle\Dns\Executor\Executor`, created by providing the constructor with the IP address of a DNS server to use to perform queries. It is recommended to use a DNS server closest to you, such as the local router. If this is not possible, Google operates two DNS server that also can be used at `8.8.8.8` and `8.8.4.4`.

```php
use Icicle\Dns\Executor\Executor;

$executor = new Executor('8.8.8.8');
```

The `Icicle\Dns\Executor\Executor` constructor also accepts an instance of `Icicle\Socket\Client\Connector` as the second argument if custom behavior is desired when connecting to the name server. If no instance is given, one is automatically created.

### Using an Executor

Once created, an executor is used by calling the `execute()` method with the domain and type of DNS query to be performed. The type may be a case-insensitive string naming a record type (e.g., `'A'`, `'MX'`, `'NS'`, `'PTR'`, `'AAAA'`) or the integer value corresponding to a record type (`LibDNS\Records\ResourceQTypes` defines constants corresponding to a the integer value of a type). `execute()` returns a coroutine fulfilled with an instance of `LibDNS\Messages\Message` that represents the response from the name server. `LibDNS\Messages\Message` objects have several methods that will need to be used to fetch the data in the response.

- `getAnswerRecords()`: Returns an instance of `LibDNS\Records\RecordCollection`, a traversable collection of `LibDNS\Record\Resource` objects containing the response answer records.
- `getAuthorityRecords()`: Returns an instance of `LibDNS\Records\RecordCollection` containing the response authority records.
- `getAdditionalRecords()`: Returns an instance of `LibDNS\Records\RecordCollection` containing the response additional records.
- `getAuthorityRecords()`: Returns an instance of `LibDNS\Records\RecordCollection` containing the response authority records.
- `isAuthoritative()`: Determines if the response is authoritative for the records returned.

DNS records in the traversable `LibDNS\Records\RecordCollection` objects are represented as instances of `LibDNS\Records\Resource`. These objects have several methods to access the data associated with the record.

- `getType()`: Returns the record type as an `integer`.
- `getName()`: Gets the domain name associated with the record as a `string`.
- `getData()`: Returns an `LibDNS\Records\RData` instance representing the records data. This object may be cast to a `string` or each field can be accessed with the `LibDNS\Records\RData::getField(int $index)` method. The number of fields in a resource depends on the type of resource (e.g., `MX` records contain two fields, a priority and a host name).
- `getTTL()`: Gets the TTL (time-to-live) as an `integer`.

Below is an example of how an executor can be used to find the NS records for a domain.

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Dns\Executor\Executor;
use Icicle\Loop;
use LibDNS\Messages\Message;

$executor = new Executor('8.8.8.8');

$coroutine = new Coroutine($executor->execute('google.com', 'NS'));

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

### MultiExecutor

The `Icicle\Dns\Executor\MultiExecutor` class can be used to combine multiple executors to send queries to several name servers so queries can be resolved even if some name servers stop responding.

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

Queries using the above executor will automatically send requests to the second name server if the first does not respond. Subsequent queries are initially sent to the last server that successfully responded to a query.



## Resolver

A resolver finds the IP addresses for a given domain. `Icicle\Dns\Resolver\Resolver` implements `Icicle\Dns\Resolver\ResolverInterface`, which defines a single method, [`resolve()`](../api/dns.md#resolve). A resolver is essentially a specialized executor that performs only `'A'` queries, fulfilling the coroutine returned from `resolve()` with an array of IP addresses (even if only one or zero IP addresses is found, the coroutine is still resolved with an array).

The `Icicle\Resolver\Resolver` class is constructed by passing an `Icicle\Executor\ExecutorInterface` instance that is used to execute queries to resolve domains. If no executor is given, one will be created by default, using `8.8.8.8` and `8.8.4.4` as DNS servers for the executor.

##### Example

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



## Connector

The connector component connects to a server by first resolving the hostname provided, then making the connection and resolving the returned coroutine with an instance of `Icicle\Socket\Client\ClientInterface`. `Icicle\Dns\Connector\Connector` implements `Icicle\Socket\Client\ConnectorInterface` and `Icicle\Dns\Connector\ConnectorInterface`, allowing it to be used anywhere a standard connector (`Icicle\Socket\Client\ConnectorInterface`) is required, or allowing components to require a resolving connector (`Icicle\Dns\Connector\ConnectorInterface`).

`Icicle\Dns\Connector\ConnectorInterface` defines a single method, [`connect()`](../api/dns.md#connect) that should resolve a host name and connect to one of the resolved servers, resolving the coroutine with the connected client.

`Icicle\Dns\Connector\Connector` will attempt to connect to one of the IP addresses found for a given host name. If the server at that IP is unresponsive, the connector will attempt to establish a connection to the next IP in the list until a server accepts the connection. Only if the connector is unable to connect to all of the IPs will it reject the coroutine returned from `connect()`. The constructor also optionally accepts an instance of `Icicle\Socket\Client\ConnectorInterface` if custom behavior is desired when connecting to the resolved host.

##### Example

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
