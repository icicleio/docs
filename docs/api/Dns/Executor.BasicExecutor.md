The default executor implementation

**Implements**
:   [`Icicle\Dns\Executor\Executor`](Executor.Executor.md)


## __construct()

    new BasicExecutor(
        string $address,
        int $port = Executor::DEFAULT_PORT,
        Icicle\Socket\Connector\Connector $connector = null
    )

Constructs a new DNS executor.

### Parameters
`string $address`
:   The IP address of the DNS resolver to use.

`int $port = 53`
:   The port to connect to the DNS server. Defaults to `Executor::DEFAULT_PORT` which has the value `53`, the standard port for the DNS protocol.

`Icicle\Socket\Connector\Connector $connector = null`
:   A socket connector instance to use to connect to the DNS server socket. If left as `null`, the [default socket connector](../Socket/index.md#connector) is used.


## getAddress()

    Executor::getAddress(): string

Gets the IP address of the DNS server used by this executor.

### Return value
`string`
:   The server IP address.


## getPort()

    Executor::getPort(): int

Gets the port of the DNS server used by this executor.

### Return value
`int`
:   The server port.
