The `Icicle\Socket\Connector\DefaultConnector` class (implements `Icicle\Socket\Connector\Connector`) asynchronously connects to a remote server, returning a coroutine that is fulfilled with an instance of `Icicle\Socket\Socket` when the connection is successfully established.

!!! warning
    The *host should be given as an IP address*, as DNS lookups performed by PHP are synchronous (blocking). If you wish to use domain names instead of IPs, see [`Icicle\Dns\Connector\Connector`](../Dns/Connector.Connector.md).


## connect()

    Connector::connect(
        string $host,
        int|null $port = null,
        mixed[] $options = []
    ): \Generator

Connects asynchronously to the given IP or unix socket path on the given port number (`null` for unix socket).

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
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

### Resolution value
`Icicle\Socket\Socket`
:   Fulfilled with once the connection is established.

### Rejection reasons
`Icicle\Socket\Exception\FailureException`
:   If the connection attempt fails (such as an invalid host).

`Icicle\Awaitable\Exception\TimeoutException`
:   If the connection attempt times out.

!!! tip
    See <http://curl.haxx.se/docs/caextract.html> for links to download a bundle of CA Root Certificates that may be used for the cafile option if needed.
