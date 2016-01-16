When connecting sockets, we normally must specify the IP address of the computer to connect to. What if we want to connect to a server by its domain name? To look up the IP address of a server by its domain name, we must perform a *DNS query*. We can use DNS queries to *resolve* a domain name and find the corresponding IP address so we can connect to it.

PHP already provides means of resolving domain names (using the [`gethostbyname()`](http://php.net/gethostbyname) function for example), but those functions are synchronous and use blocking I/O. Sometimes DNS queries can cause a noticeable delay. To solve this problem, Icicle provides asynchronous DNS executors so our code doesn't block when connecting through a domain name.


#### Example

The example below uses a resolver to asynchronously find the IP address for the domain `icicle.io`.

```php
use Icicle\Coroutine;
use Icicle\Dns;
use Icicle\Loop;

Coroutine\create(function () {
    try {
        $ips = (yield Dns\resolve('icicle.io'));

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


## Executor

Executors are the foundation of the DNS package, performing any DNS query and returning the full results of that query. [Resolvers](#resolver) and [connectors](#connector) depend on executors to perform the DNS query required for their operation.

### Using an Executor

The global DNS executor (see the [DNS API docs](../api/Dns/Executor.Executor.md) for information on setting the global executor if desired) may used by calling the `Icicle\Dns\execute()` function with the domain and type of DNS query to be performed. The type may be a case-insensitive string naming a record type (e.g., `'A'`, `'MX'`, `'NS'`, `'PTR'`, `'AAAA'`) or the integer value corresponding to a record type (`LibDNS\Records\ResourceQTypes` defines constants corresponding to a the integer value of a type). `execute()` returns a coroutine fulfilled with an instance of `LibDNS\Messages\Message` that represents the response from the name server. `LibDNS\Messages\Message` objects have several methods that will need to be used to fetch the data in the response.

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
use Icicle\Dns;
use Icicle\Loop;
use LibDNS\Messages\Message;

$coroutine = new Coroutine(Dns\execute('google.com', 'NS'));

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

## Resolver

A resolver finds the IP addresses for a given domain. A resolver is essentially a specialized executor that performs only `A` or `AAAA` queries, fulfilling the coroutine returned from `resolve()` with an array of IP addresses (even if only one or zero IP addresses is found, the coroutine is still resolved with an array).

The global DNS resolver (see the [DNS API docs](../api/Dns/Resolver.Resolver.md) for information on setting the global resolver if desired) may used by calling the `Icicle\Dns\resolve()` function with the domain name to resolve.

##### Example

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Dns;
use Icicle\Loop;

$coroutine = new Coroutine(Dns\resolve('google.com'));

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

Connectors first resolve the hostname provided, then make a connection to the resolved IP address, resolving the coroutine with an instance of `Icicle\Socket\Socket`. `Icicle\Dns\Connector\Connector` extends `Icicle\Socket\Connector\Connector`, allowing it to be used anywhere a standard connector is required or allowing components to require a resolving connector (generally recommended).

`Icicle\Dns\Connector\Connector` defines a single method, [`connect()`](../api/Dns/Connector.Connector.md#connect) that should resolve a host name and connect to one of the resolved servers, resolving the coroutine with the connected client.

`Icicle\Dns\Connector\DefaultConnector` will attempt to connect to one of the IP addresses found for a given host name. If the server at that IP is unresponsive, the connector will attempt to establish a connection to the next IP in the list until a server accepts the connection. Only if the connector is unable to connect to all of the IPs will it reject the coroutine returned from `connect()`. The constructor also optionally accepts an instance of `Icicle\Socket\Connector\Connector` if custom behavior is desired when connecting to the resolved host.

Generally code can rely on the global connector (see the [DNS API docs](../api/Dns/Resolver.Resolver.md) for information on setting the global connector if desired) using the `Icicle\Dns\connect()` function to make network connections.

##### Example

```php
use Icicle\Dns;
use Icicle\Loop;
use Icicle\Socket\Socket;

$coroutine = new Coroutine(Dns\connect('google.com', 80));

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
