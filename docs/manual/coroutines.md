**[Coroutine API documentation](../api/coroutine.md)**

Coroutines are interruptible functions implemented using [Generators](http://www.php.net/manual/en/language.generators.overview.php). A `Generator` usually uses the `yield` keyword to yield a value from a set to implement an iterator. Coroutines use the `yield` keyword to define interruption points. When a coroutine yields a value, execution of the coroutine is temporarily interrupted, allowing other tasks to be run, such as I/O, timers, or other coroutines.

When a coroutine yields a [promise](promises.md), execution of the coroutine is interrupted until the promise is resolved. If the promise is fulfilled with a value, the yield statement that yielded the promise will take on the resolved value. For example, `$value = (yield Icicle\Promise\resolve(2.718));` will set `$value` to `2.718` when execution of the coroutine is resumed. If the promise is rejected, the exception used to reject the promise will be thrown into the function at the yield statement. For example, `yield Icicle\Promise\reject(new Exception());` would behave identically to replacing the yield statement with `throw new Exception();`.

Note that **no callbacks need to be registered** with the promises yielded in a coroutine and **errors are reported using thrown exceptions**, which will bubble up to the calling context if uncaught in the same way exceptions bubble up in synchronous code.

The example below creates an `Icicle\Coroutine\Coroutine` instance from a function returning a `Generator`. (`Icicle\Dns\Connector\Connector` in the [DNS component](../api/dns.md) uses a coroutine structured similarly to the one below, except it attempts to connect to other IPs returned from the resolver if the first one fails.)

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Dns\Executor\Executor;
use Icicle\Dns\Resolver\Resolver;
use Icicle\Loop;
use Icicle\Socket\Client\Connector;

$generator = function () {
    try {
        $resolver = new Resolver(new Executor('8.8.8.8'));

        // This coroutine pauses until yielded coroutine is fulfilled or rejected.
        $ips = (yield $resolver->resolve('example.com'));

        $connector = new Connector();

        // This coroutine pauses again until yielded coroutine is fulfilled or rejected.
        $client = (yield $connector->connect($ips[0], 80));

        echo "Asynchronously connected to example.com:80\n";
    } catch (Exception $exception) {
        echo "Asynchronous task failed: {$exception->getMessage()}\n";
    }
};

$coroutine = new Coroutine($generator());

Loop\run();
```

The example above does the same thing as the example in the section on [promises](../api/promise.md) above, but instead uses a coroutine to **structure asynchronous code like synchronous code**. Fulfillment values of promises are accessed through simple variable assignments and exceptions used to reject promises are caught using a try/catch block, rather than creating and registering callback functions to each promise.

**`Icicle\Coroutine\Coroutine` instances are also [promises](promises.md), implementing `Icicle\Promise\PromiseInterface`.** The coroutine is fulfilled with the last value yielded from the generator (or fulfillment value of the last yielded promise) or rejected if an exception is thrown from the generator. A coroutine may then yield other coroutines, suspending execution until the yielded coroutine has resolved. If a coroutine yields a `Generator`, it will automatically be converted to a `Coroutine` and handled in the same way as a yielded coroutine.


## Cooperation

Coroutines automatically are cooperative, allowing other code to execute once the coroutine has yielded a value.

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Loop;

$generator = function ($id, $count = 0) {
    for ($i = 0; $count > $i; ++$i) {
        $data = (yield "[{$id}]");
        echo $data;
    }
};

$coroutine1 = new Coroutine($generator(1, 8));
$coroutine2 = new Coroutine($generator(2, 5));
$coroutine3 = new Coroutine($generator(3, 2));

Loop\run();
```

The example above will output the string `[1][2][3][1][2][3][1][2][1][2][1][2][1][1][1]` since each coroutine cooperates with each other running coroutines (as well as other tasks in the loop, such as I/O, timers, and signals).



## Controlling Execution
Coroutines begin execution immediately upon construction.

Coroutine objects have some methods for controlling execution once they are created. See the API documentation for [`Icicle\Coroutine\CoroutineInterface`](../api/coroutine.md#coroutineinterface) for available methods.
