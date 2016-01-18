Interface for factories that can be used to create datagram instances from a hostname or unix socket path, port number (`null` for unix socket), and list of options.


## create()

    DatagramFactory::create(
        string $host,
        int $port,
        mixed[] $options = []
    ): Icicle\Socket\Datagram\Datagram

Creates a datagram bound and listening on the given IP and port number. No options are defined in this implementation.

### Parameters
`string $host`
:   IP address of receiver.

`int $port`
:   Port of receiver.

`mixed[] $options = []`
:   Array of additional options.
