Executors are the foundation of the DNS component, performing any DNS query and returning the full results of that query. Resolvers and connectors depend on executors to perform the DNS query required for their operation.


## execute()

    Executor::execute(
        string $domain,
        string|int $type,
        mixed[] $options = []
    ): \Generator

Executes a DNS query.

An executor will retry a query a number of times if it doesn't receive a response within `timeout` seconds. The number of times a query will be retried before failing is defined by `retries`, with `timeout` seconds elapsing between each query attempt.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
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

### Resolution value
`LibDNS\Messages\Message`
:   Response message.

### Rejection reasons
`Icicle\Dns\Exception\FailureException`
:   If sending the request or parsing the response fails.

`Icicle\Dns\Exception\MessageException`
:   If the server returns a non-zero response code or no response is received from the server.
