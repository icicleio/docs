This optional package provides an asynchronous DNS query executor, resolver, and client connector. The package is available on [Packagist](https://packagist.org) as [`icicleio/dns`](https://packagist.org/packages/icicleio/dns).

This package provides a set of global functions in the `Icicle\Dns` namespace to perform asynchronous DNS queries that should be sufficient for most applications. However, if desired an application may customize the methods used to perform DNS queries using the objects described below.

Since most applications don't need specialized DNS executors, several functions are provided that allow you to work with a global DNS executor instance.

## execute()

    Icicle\Dns\execute(
        string $name,
        string|int $type,
        mixed[] $options = []
    ): \Generator

Uses the global executor to execute a DNS query.

See [`Executor::execute()`](Executor.Executor.md#execute) for details on how to call the execute function.


## executor()

Accesses and sets the global executor instance.

    Icicle\Dns\executor(
        Icicle\Dns\Executor\Executor|null $executor = null
    ): Icicle\Dns\Executor\Executor

### Parameters
`Icicle\Dns\Executor\Executor|null $executor`
:   The executor to set, as the global instance, or `null` to use the current instance.

### Return value
`Icicle\Dns\Executor\Executor`
:   The global executor instance.


## resolve()

    Icicle\Dns\resolve(
        string $domain,
        mixed[] $options = []
    ): \Generator

Uses the global resolver to resolve the IP address of a domain name.

See [`Resolver::resolve()`](Resolver.Resolver.md#resolve) for details on how to call the resolve function.


## resolver()

    Icicle\Dns\resolver(
        Icicle\Dns\Resolver\Resolver|null $resolver = null
    ): Icicle\Dns\Resolver\Resolver

Accesses and sets the global resolver instance.

### Parameters
`Icicle\Dns\Resolver\Resolver|null $resolver = null`
:   The resolver to set, as the global instance, or `null` to use the current instance.

### Return value
`Icicle\Dns\Resolver\Resolver`
:   The global resolver instance.


## connect()

    Icicle\Dns\connect(
        string $domain,
        int $port,
        mixed[] $options = []
    ): \Generator

Uses the global connector to connect to the domain on the given port.

See [`Connector::connect()`](Connector.Connector.md#connect) for details on how to call the connect function.


## connector()

    Icicle\Dns\connector(
        Icicle\Dns\Connector\Connector|null $connector = null
    ): Icicle\Dns\Connector\Connector

Accesses and sets the global connector instance.

### Parameters
`Icicle\Dns\Connector\Connector|null $connector = null`
:   The connector to set, as the global instance, or `null` to use the current instance.

### Return value
`Icicle\Dns\Connector\Connector`
:   The global connector instance.
