A pool of workers that can be used to execute multiple tasks synchronously.

**Extends**
:   [`Worker\Worker`](Worker.Worker.md)

A worker pool is a collection of worker threads that can perform multiple tasks simultaneously. The load on each worker is balanced such that tasks are completed as soon as possible and workers are used efficiently.


## getWorkerCount()

    Pool::getWorkerCount(): int

Gets the number of workers currently running in the pool.

### Return value
The number of workers.


## getIdleWorkerCount()

    Pool::getIdleWorkerCount(): int

Gets the number of workers that are currently idle.

### Return value
The number of idle workers.


## getMinSize()

    Pool::getMinSize(): int

Gets the minimum number of workers the pool may have idle.

### Return value
The minimum number of workers.


## getMaxSize()

    Pool::getMaxSize(): int

Gets the maximum number of workers the pool may spawn to handle concurrent tasks.

### Return value
The maximum number of workers.


## push()

    Pool::push(Worker $worker)

Pushes a worker into the pool, marking it as idle and available to be pulled from the pool again.

### Parameters
`Icicle\Concurrent\Worker\Worker $worker`
:   The worker to push.

### Exceptions
`Icicle\Concurrent\Exception\StatusError`
:   If the pool is not running.

`Icicle\Exception\InvalidArgumentError`
:   If the given worker is not part of this pool or was already pushed into the pool.


## pull()

    Pool::pull(): Worker

Pulls a worker from the pool. The worker is marked as busy and will only be reused if the pool runs out of idle workers.

### Exceptions
`Icicle\Concurrent\Exception\StatusError`
:   If the pool is not running.

### Return value
A worker pulled from the pool.
