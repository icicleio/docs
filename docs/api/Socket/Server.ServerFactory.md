`Icicle\Socket\Server\DefaultServerFactory` (implements `Icicle\Socket\Server\ServerFactory`) can be used to create server instances from a IP or unix socket path, port number (`null` for unix socket), and list of options.

### create()

```php
ServerFactory::create(
    string $host,
    int|null $port = null,
    mixed[] $options = []
): Icicle\Socket\Server\Server
```

Creates a server bound and listening on the given ip or unix socket path and port number (`null` for unix socket).

#### Parameters
`string $host`
:   IP address or unix socket path.

`int|null $port = null`
:   Port number or null for unix socket.

`mixed[] $options = []`
:   An associative array of server socket options. The possible options are given below.

    Option | Type | Description
    :-- | :-- | :--
    `backlog` | `int` | Connection backlog size. Note that the operating system variable `SOMAXCONN` may set an upper limit and may need to be changed to allow a larger backlog size.
    `pem` | `string` | Path to PEM file containing certificate and private key to enable SSL on client connections.
    `passphrase` | `string` | PEM passphrase if applicable.
    `name` | `string` | Name to use as SNI identifier. If not set, name will be guessed based on `$host`.
    `verify_peer` | `bool` | True to verify client certificate. Normally should be false on the server.
    `allow_self_signed` | `bool` | Set to true to allow self-signed certificates. Defaults to false.
    `verify_depth` | `int` | Max levels of certificate authorities the verifier will transverse. Defaults to 10.
