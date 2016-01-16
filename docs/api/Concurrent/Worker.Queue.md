### pull()

    Queue::pull(): Worker

Pulls a worker from the queue. The worker is marked as busy and will only be reused if the queue runs out of idle workers.

#### Exceptions
`Icicle\Concurrent\Exception\StatusError`
:   If the queue is not running.

#### Return value
A worker pulled from the queue.


### push()

    Queue::push(Worker $worker)

Pushes a worker into the queue, marking it as idle and available to be pulled from the queue again.

#### Parameters
`Icicle\Concurrent\Worker\Worker $worker`
:   The worker to push.

#### Exceptions
`Icicle\Concurrent\Exception\StatusError`
:   If the queue is not running.

`Icicle\Exception\InvalidArgumentError`
:   If the given worker is not part of this queue or was already pushed into the queue.


### getWorkerCount()

    Pool::getWorkerCount(): int

Gets the number of workers currently running in the queue.

#### Return value
The number of workers.


### getIdleWorkerCount()

    Pool::getIdleWorkerCount(): int

Gets the number of workers that are currently idle.

#### Return value
The number of idle workers.


### getMinSize()

    Pool::getMinSize(): int

Gets the minimum number of workers the queue may have idle.

#### Return value
The minimum number of workers.


### getMaxSize()

    Pool::getMaxSize(): int

Gets the maximum number of workers the queue may spawn to handle concurrent tasks.

#### Return value
The maximum number of workers.
