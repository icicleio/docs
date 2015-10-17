Icicle provides a means of parallelizing code without littering your application with complicated lock checking and inter-process communication.

To use Icicle's concurrency component, first install the `icicleio/concurrent` package:

```bash
composer require icicleio/concurrent
```

To be as flexible as possible, this package comes with a collection of non-blocking concurrency tools that can be used independently as needed, as well as an "opinionated" worker API that allows you to assign units of work to a pool of worker threads or processes.

There are two primary ways to parallelize code in Icicle: multithreading, or multiple processes. Threading provides better performance and is compatible with Unix and Windows but requires ZTS (Zend thread-safe) PHP. Multiprocessing can be accomplished with either forking or using [`proc_open()`](http://php.net/proc_open). Forking has fewer disadvantages, but is only compatible with Unix.

Threading is generally preferred over multiple processes, but because of all these complications, Icicle supports all three methods.

## Threads

Threading is a cross-platform concurrency method that is fast and memory efficient. Thread contexts take advantage of an operating system's multi-threading capabilities to run code in parallel. A spawned thread will run completely parallel to the parent thread, each with its own environment. Each thread is assigned a closure to execute when it is created, and the returned value is passed back to the parent thread. Icicle goes for a "shared-nothing" architecture, so any variables inside the closure are local to that thread and can store any non-safe data.

You can spawn a new thread with the `Thread::spawn()` method:

```php
use Icicle\Concurrent\Threading\Thread;
use Icicle\Coroutine;
use Icicle\Loop;

Coroutine\create(function () {
    $thread = Thread::spawn(function () {
        print "Hello, World!\n";
    });

    yield $thread->join();
});

Loop\run();
```

You can wait for a thread to finish by calling `join()`. Joining does not block the parent thread and will asynchronously wait for the child thread to finish before resolving.

## Forks

For Unix-like systems, you can create parallel execution using fork contexts. Though not as efficient as multi-threading, in some cases forking can take better advantage of some multi-core processors than threads. Fork contexts use the `pcntl_fork()` function to create a copy of the current process and run alternate code inside the new process.

Spawning and controlling forks are quite similar to creating threads. To spawn a new fork, use the `Fork::spawn()` method:

```php
use Icicle\Concurrent\Forking\Fork;
use Icicle\Coroutine;
use Icicle\Loop;

Coroutine\create(function () {
    $fork = Fork::spawn(function () {
        print "Hello, World!\n";
    });

    yield $fork->join();
});

Loop\run();
```

Calling `join()` on a fork will asynchronously wait for the forked process to terminate, similar to the `pcntl_wait()` function.

## Synchronization with Channels

Threads and forks wouldn't be very useful if they couldn't be given any data to work on. The recommended way to share data between contexts is with a `Channel`. A channel is a low-level abstraction over local, non-blocking sockets, which can be used to pass messages and objects between two contexts. Channels are non-blocking and do not require locking. For example:

```php
use Icicle\Concurrent\Sync\Channel;
use Icicle\Concurrent\Threading\Thread;
use Icicle\Coroutine;
use Icicle\Loop;

Coroutine\create(function () {
    $thread = Thread::spawn(function () {
        $time = (yield $this->receive()); // Receive from the parent.
        sleep($time);
        yield $this->send("Hello!"); // Send to the parent.
    });

    yield $thread->send(3); // Send 3 to the context.

    $message = (yield $thread->receive()); // Receive from the context.
    yield $thread->join();

    print $message . "\n";
});

Loop\run();
```

Thread and fork execution contexts include a channel to communicate with the parent and context. The channel methods `send()` and `receive()` may be invoked using `$this` within the function executed in the context and on the context object in the parent. See the example above.

## Synchronization with Parcels

Parcels are shared containers that allow you to store context-safe data inside a shared location so that it can be accessed by multiple contexts. To prevent race conditions, you still need to access a parcel's data exclusively, but Icicle allows you to acquire a lock on a parcel asynchronously without blocking the context execution, unlike traditional mutexes.

Below is an example of sharing a `SplQueue` of values between threads:

```php
use Icicle\Concurrent\Threading\Parcel;
use Icicle\Concurrent\Threading\Thread;
use Icicle\Coroutine;
use Icicle\Loop;
use Icicle\Promise;

Coroutine\create(function () {
    // Create a queue and wrap it in a shareable, thread-safe parcel.
    $queue = new Parcel(new \SplQueue());

    // Spawn multiple threads and collect their handles in an array.
    $threads = [];
    foreach (range(0, 4) as $_) {
        // Each thread is passed a handle to the parcel.
        $threads[] = Thread::spawn(function (Parcel $queue) {
            // Sleep for a second to simulate work being done.
            sleep(1);

            // Synchronize with the queue parcel so that we have exclusive access
            // in order to modify the queue.
            yield $queue->synchronized(function (Parcel $queue) {
                // Copy the object contained in the parcel into a local variable.
                $local = $queue->unwrap();

                // Enqueue a value.
                $local->enqueue("Hello!");

                // Copy the changed object back into the parcel.
                $queue->wrap($local);
            });
        }, $queue);
    }

    // Wait for all of the threads to finish.
    yield Promise\all(array_map(function (Thread $thread) {
        // Return an array of coroutines to wait on.
        return new Coroutine\Coroutine($thread->join());
    }, $threads));

    // Print out the contents of the queue.
    // This will print out "Hello!" 5 times; one from each thread.
    foreach ($queue->unwrap() as $item) {
        echo $item . "\n";
    }
});

Loop\run();
```
