The default resolver implementation that implements [`Icicle\Dns\Resolver\Resolver`](#resolverresolver).

### __construct()

    BasicResolver::__construct(
        Icicle\Dns\Executor\Executor|null $executor = null
    )

Constructs a new DNS resolver.

#### Parameters
`Icicle\Dns\Executor\Executor $executor = null`
:   Executor object to perform DNS queries. If none is provided, the default global executor will be used.
