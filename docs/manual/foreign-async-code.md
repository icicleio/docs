## Using callbacks or promises from other libraries
Several functions are provided for transforming functions into promises-aware callables. You can easily turn common callback-based code into a function returning a promise with `Promise\promisify()`:

```php
use Icicle\Promise;

function asyncWithCallback($callback)
{
    // Do stuff, then call $callback later.
    $callback("hello");
}

// Promisify the function.
$promisified = Promise\promisify('asyncWithCallback');

// Call the promisified function.
$promisified()->then(function ($greeting) {
    print $greeting;
});
```

You can also use promise objects from other libraries that implement other interfaces with [`Promise\adapt()`](). This function takes any object with a `then(callable $onFulfilled, callable $onRejected)` method and returns a new Icicle promise that wraps around the original object. Below is an example of adapting a [ReactPHP](http://reactphp.org) promise:

```php
$reactPromise = new \React\Promise\Promise(function ($resolve, $reject) {
    // Resolver
});

$iciclePromise = \Icicle\Promise\adapt($reactPromise);
```

See the [Promise API documentation](../api/promise.md) for more information on `Promise\adapt()`.



## Using the event loop in ReactPHP code

If you need to use a library designed to work with [ReactPHP](http://reactphp.org), you can use the [React adapter](https://github.com/icicleio/react-adapter) to pass in the Icicle event loop to functions that need a React event loop.

The adapter provides a class `Icicle\ReactAdapter\Loop\ReactLoop` that implements [`React\EventLoop\LoopInterface`](https://github.com/reactphp/event-loop). This React-compatible event loop is simply a wrapper around the global event loop, and serves as a direct replacement to a real React loop.

Below is an example of how we can use ['Predis\Async'](https://github.com/nrk/predis-async), a React-compatible library, with Icicle.

```php
use Icicle\ReactAdapter\Loop\ReactLoop;
use Predis\Async\Client;

// Create the loop adapter.
$loop = new ReactLoop();

// $loop can be used anywhere an instance of React\EventLoop\LoopInterface is required.
$client = new Client('tcp://127.0.0.1:6379', $loop);
```

The loop adapter implements all the React event loop features and can be used anywhere a React loop is required.

!!! note
    Since a `ReactLoop` object is just a wrapper around the *global* event loop, every `ReactLoop` object accesses the same Icicle event loop. Thus, it's not necessary to keep track of `ReactLoop` instances and each React component can use different instances if it is convenient.

For more details on how to use the adapter, see [the package's README file](https://github.com/icicleio/react-adapter/blob/master/README.md).



## Using promises in ReactPHP code

If you need to create a promise that React code needs to wait on, the adapter package also provides `Icicle\ReactAdapter\Promise\ReactPromise`, which implements `React\Promise\ExtendedPromiseInterface` and provides a bridge to Icicle promises.

```php
$iciclePromise = new \Icicle\Promise\Promise(function ($resolve, $reject) {
    // Resolver
});

$reactPromise = new \Icicle\ReactAdapter\Promise\ReactPromise($iciclePromise);
```
