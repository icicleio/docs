The default resolver implementation.

**Implements**
:   [`Icicle\Dns\Resolver\Resolver`](Resolver.Resolver.md)


## __construct()

    new BasicResolver(
        Icicle\Dns\Executor\Executor|null $executor = null
    )

Constructs a new DNS resolver.

#### Parameters
`Icicle\Dns\Executor\Executor $executor = null`
:   Executor object to perform DNS queries. If none is provided, the default global executor will be used.
