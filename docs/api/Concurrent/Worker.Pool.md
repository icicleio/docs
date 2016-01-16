A pool of workers that can be used to execute multiple tasks synchronously.

A worker pool is a collection of worker threads that can perform multiple tasks simultaneously. The load on each worker is balanced such that tasks are completed as soon as possible and workers are used efficiently.


### getWorkerCount()

    Pool::getWorkerCount(): int

Gets the number of workers currently running in the pool.

#### Return value
The number of workers.


### getIdleWorkerCount()

    Pool::getIdleWorkerCount(): int

Gets the number of workers that are currently idle.

#### Return value
The number of idle workers.


### getMinSize()

    Pool::getMinSize(): int

Gets the minimum number of workers the pool may have idle.

#### Return value
The minimum number of workers.


### getMaxSize()

    Pool::getMaxSize(): int

Gets the maximum number of workers the pool may spawn to handle concurrent tasks.

#### Return value
The maximum number of workers.
