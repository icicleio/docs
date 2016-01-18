A resolver finds the IP addresses for a given domain. A resolver is essentially a specialized executor that performs only `A` or `AAAA` queries (defaults to `A` queries, use `'mode' => Resolver::IPv6` in the `$options` array for `AAAA` records).

The default implementation is `Icicle\Dns\Resolver\BasicResolver`, which is constructed by passing an `Icicle\Dns\Executor\Executor` instance to the constructor that is used to execute queries to resolve domains. If no executor is given, the global executor instance is used.


## resolve()

    Resolver::resolve(
        string $domain,
        array $options = []
    ): \Generator

Resolves a given domain and yields an array of IP addresses that match the given domain.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
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

### Resolution value
`string[]`
:   Array of resolved IP addresses. May be empty if the query is successful but no IP addresses could be found.

### Rejection reasons
`Icicle\Dns\Exception\FailureException`
:  If sending the request or parsing the response fails.

`Icicle\Dns\Exception\MessageException`:
:   If the server returns a non-zero response code or no response is received.

!!! note
    Even if there is only one or no matches at all for the given domain name, the return value will still resolve with an array if the DNS query itself was successful.

### Example

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
