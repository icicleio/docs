An asynchronous semaphore based on pthreads' synchronization methods. Implements [`Icicle\Concurrent\Sync\Semaphore`](#syncsemaphoreinterface).

This is an implementation of a thread-safe semaphore that has non-blocking acquire methods. There is a small tradeoff for asynchronous semaphores; you may not acquire a lock immediately when one is available and there may be a small delay. However, the small delay will not block the thread.
