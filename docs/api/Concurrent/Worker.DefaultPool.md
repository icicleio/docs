The default worker pool implementation.

**Implements**
:   [`Worker\Pool`](Worker.Pool.md)


## __construct()

    new DefaultPool(
        int $minSize = null,
        int $maxSize = null,
        WorkerFactory $factory = null
    )

Creates a new worker pool.

#### Parameters
`int|null $minSize`
:   The minimum number of workers the pool should spawn. Defaults to `Pool::DEFAULT_MIN_SIZE`.

`int|null $maxSize`
:   The maximum number of workers the pool should spawn. Defaults to `Pool::DEFAULT_MAX_SIZE`.

`Icicle\Concurrent\Worker\WorkerFactory|null $factory`
:   A worker factory to be used to create new workers.
