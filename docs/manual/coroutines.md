Coroutines are interruptible functions implemented using [Generators](http://www.php.net/manual/en/language.generators.overview.php). A `Generator` usually uses the `yield` keyword to yield a value from a set to implement an iterator. Coroutines use the `yield` keyword to define interruption points. When a coroutine yields a value, execution of the coroutine is temporarily interrupted, allowing other tasks to be run, such as I/O, timers, or other coroutines.

When a coroutine yields a [awaitable](awaitables.md), execution of the coroutine is interrupted until the awaitable is resolved. If the awaitable is fulfilled with a value, the yield statement that yielded the awaitable will take on the resolved value. For example, `$value = (yield Icicle\Awaitable\resolve(2.718));` will set `$value` to `2.718` when execution of the coroutine is resumed. If the awaitable is rejected, the exception used to reject the awaitable will be thrown into the function at the yield statement. For example, `yield Icicle\Awaitable\reject(new \Exception());` would behave identically to replacing the yield statement with `throw new \Exception();`.

Note that **no callbacks need to be registered** with the awaitables yielded in a coroutine and **errors are reported using thrown exceptions**, which will bubble up to the calling context if uncaught in the same way exceptions bubble up in synchronous code.

The example below creates an `Icicle\Coroutine\Coroutine` instance from a function returning a `Generator`. (`Icicle\Dns\Connector\Connector` in the [DNS component](../api/dns.md) uses a coroutine structured similarly to the one below, except it attempts to connect to other IPs returned from the resolver if the first one fails.)

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Dns\Executor\BasicExecutor;
use Icicle\Dns\Resolver\BasicResolver;
use Icicle\Loop;
use Icicle\Socket\Client\DefaultConnector;

$generator = function () {
    try {
        $resolver = new BasicResolver(new BasicExecutor('8.8.8.8'));

        // Coroutine pauses until yielded coroutine is fulfilled or rejected.
        $ips = (yield $resolver->resolve('example.com'));

        $connector = new DefaultConnector();

        // Coroutine pauses again until yielded coroutine is fulfilled or rejected.
        $client = (yield $connector->connect($ips[0], 80));

        echo "Asynchronously connected to example.com:80\n";
    } catch (Exception $exception) {
        echo "Asynchronous task failed: {$exception->getMessage()}\n";
    }
};

$coroutine = new Coroutine($generator());

Loop\run();
```

The example above does the same thing as the example in the section on [awaitables](../api/awaitable.md), but instead uses a coroutine to **structure asynchronous code like synchronous code**. Fulfillment values of awaitables are accessed through simple variable assignments and exceptions used to reject awaitables are caught using a try/catch block, rather than creating and registering callback functions to each awaitable.

**`Icicle\Coroutine\Coroutine` instances are also [awaitables](awaitables.md), implementing `Icicle\Awaitable\Awaitable`.** The coroutine is fulfilled with the last value yielded from the generator (or fulfillment value of the last yielded awaitable) or rejected if an exception is thrown from the generator ([note in v2.0 (PHP 7 only) return is used to fulfill a coroutine](#coroutines-in-v1x-vs-v2x)). A coroutine may then yield other coroutines, suspending execution until the yielded coroutine has resolved. If a coroutine yields a `\Generator`, it will automatically be converted to a `Coroutine` and handled in the same way as a yielded coroutine. APIs in Icicle have methods that return `\Generator` objects that *must* be yielded in a coroutine or wrapped with `new Coroutine()` to create an awaitable (see warning box below).

!!! warning
    Functions or methods in Icicle returning a `\Generator` written as a coroutine (noted with a box in these docs or [`@coroutine` in docblocks](#coroutine-docblock-annotations) within the source) must be either yielded in another coroutine (`$result = (yield coroutineFunction());`) or used with the `Coroutine` constructor (`$coroutine = new Coroutine(coroutineFunction());`). If neither of these methods is used, the generator returned from the function or method is never used and the code within the coroutine is not executed.

## Creating Coroutines
A `Coroutine` instance can also be created in a few other different ways depending on your needs.

### Coroutine Constructor

    $coroutine = new Coroutine(\Generator $generator)

As shown in the examples above, a `Icicle\Coroutine\Coroutine` instance can be created by passing a `\Generator` instance to the constructor. The coroutine constructor is often used when you wish to create an awaitable object from a function or method returning a `\Generator` written to be a coroutine (noted with a box in these docs or `@coroutine` in docblocks within the source).

### wrap()

    Coroutine\wrap(
        callable(mixed ...$args): \Generator $callback
    ): callable(mixed ...$args): Coroutine

Returns a `callable` that returns a `Icicle\Coroutine\Coroutine` by calling `$callback` that must return a `\Generator` written to be a coroutine. Any arguments given to the returned callable are also passed to `$callback`.

#### Parameters
`callable(mixed ...$args): \Generator $callback`
:   A generator function to create a coroutine function from.

#### Return value
`callable(mixed ...$args): Coroutine`
:   A callable function that returns a Coroutine object.

```php
use Icicle\Coroutine;
use Icicle\Loop;

$callback = Coroutine\wrap(function ($value) {
    $value = (yield $value + 1);
    echo '{' . $value . '}';
    $value = (yield $value + 1);
    echo '{' . $value . '}';
    $value = (yield $value + 1);
    echo '{' . $value . '}';
});

$callback(10);
$callback(20);
$callback(30);

Loop\run();
```

The example above will output `{11}{21}{31}{12}{22}{32}{13}{23}{33}`, demonstrating how a generator function can be used to create multiple coroutines. This example also demonstrates the cooperative execution of coroutines.


### create()

    Coroutine\create(
        callable(mixed ...$args): \Generator $callback,
        mixed ...$args
    ): Coroutine

Creates and runs a new coroutine from a given generator function.

#### Parameters
`callable(mixed ...$args): \Generator$callback`
:   The generator function to call and start a coroutine from. The return type of the callback should be `\Generator`.

`...$args`
:   Arguments to pass to the generator function.

#### Return value
A new coroutine for the given callback function.


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

Coroutine objects have some methods for controlling execution once they are created. See the API documentation for [`Icicle\Coroutine\Coroutine`](../api/coroutine.md#coroutine) for available methods.

Coroutines are the basic units of execution in asynchronous code, and allow you to write expressive functions that return asynchronously without callbacks or boilerplate code.

Coroutines are created by functions returning a `\Generator` that define interruption points using the `yield` keyword. When a coroutine yields a value, execution of the coroutine is temporarily interrupted, allowing other tasks to be run, such as I/O, timers, or other coroutines.


## Basic Coroutine

The code below creates a `Icicle\Coroutine\Coroutine` object from a basic generator that echoes the string "Hello world!". While this example is contrived, it demonstrates how the fulfillment value of an awaitable is used in a coroutine (see [Interrupting Coroutines with Awaitables](#interrupting-coroutines-with-awaitables)).

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Loop;
use Icicle\Awaitable;

$callback = function () {
    $data = (yield Awaitable\resolve("Hello, world!"));
    echo $data . "\n";
});

$coroutine = new Coroutine($callback());

Loop\run();
```


## Returning Values from Coroutines

The `return` keyword cannot be used in generators to return a value (note this will be changing in PHP 7 and will be used in the future to return values from coroutines). However, `return` can be used without an expression to halt execution of the generator. The return value (fulfillment value) of a coroutine is the last value yielded from the coroutine (or fulfillment value of a yielded awaitable). `return` may be used anytime to halt execution of a coroutine and fulfill with the last yielded value.

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Loop;

$callback = function ($value1, $value2) {
    if (0 === $value2) {
        yield 0;
        return; // Halts execution of the coroutine.
    }
    yield $value1 / $value2;
};

// $coroutine will be fulfilled with 0.
$coroutine = new Coroutine($callback(12, 0));

$coroutine->then(function ($result) {
    echo "Result: {$result}\n";
});

Loop\run();
```


## Throwing Exceptions from Coroutines

The `throw` keyword can be used within a coroutine in the same way as any other function. Thrown exceptions can be caught within the coroutine itself or if uncaught, will reject the coroutine. If calling a coroutine from within another coroutine, the thrown exception will bubble up to the calling context (see section on [calling coroutines within another coroutine](#calling-coroutines-within-another-coroutine)).

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Loop;

$callback = function ($value1, $value2) {
    if (0 === $value2) {
        throw new Exception('Division by zero!');
    }
    yield $value1 / $value2;
};

// $coroutine will be rejected with thrown exception.
$coroutine = new Coroutine($callback(12, 0));

$coroutine->then(
    function ($result) {
        echo "Result: {$result}\n";
    },
    function (Exception $e) {
        echo "Error: {$e->getMessage()}\n";
    }
);

Loop\run();
```


## Interrupting Coroutines with Awaitables

**When a coroutine yields an [awaitable](awaitables.md), execution of the coroutine is interrupted until the awaitable is resolved.**

Resolution of a yielded awaitable results in one of two actions within the coroutine:
1. If the awaitable is fulfilled, the statement that yielded the awaitable will take on the fulfillment value. For example, `$value = (yield Icicle\Awaitable\resolve(3.14159));` will set `$value` to `3.14159` when execution of the coroutine is resumed.
2. If the awaitable is rejected, the exception used to reject the awaitable will be thrown into the function at the yield statement. For example, `yield Icicle\Awaitable\reject(new Exception());` would behave identically to replacing the yield statement with `throw new Exception();`.

The example below yields a pending awaitable created by `Icicle\Awaitable\Awaitable::delay()` that is automatically resolved with the value given when creating the generator.

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Loop;
use Icicle\Awaitable;

$callback = function ($value) {
    $awaitable = Awaitable\resolve($value);

    // Delays coroutine execution for 1 second.
    yield $awaitable->delay(1);
};

$coroutine = new Coroutine($callback(3.14159));

$coroutine->then(function ($result) {
    echo "Result: {$result}\n";
});

Loop\run();
```

The above example is a simple contrived example to demonstrate how easy it is to write cooperative, asynchronous code using awaitables. The example below pipes data read from a readable stream into a writable stream, waiting for data to be available on the readable stream, then waiting until the data is successfully written to the writable stream before attempting to read more data from the readable stream.

```php
use Icicle\Coroutine;
use Icicle\Loop;
use Icicle\Socket\ReadableStream;
use Icicle\Socket\WritableStream;

// $readable is a ReadableStream instance.
// $writable is a WritableStream instance.

$coroutine = Coroutine\create(
    function (ReadableStream $readable, WritableStream $writable) {
        $bytes = 0;
        try {
            for (;;) {
                $data = (yield $readable->read());
                $bytes += strlen($data);
                yield $writable->write($data);
            }
        } catch (Exception $e) {
            $readable->close();
            $writable->close();
        }
        yield $bytes;
    },
    $readable,
    $writable
);

Loop\run();
```


## Calling Coroutines Within Another Coroutine

Since `Coroutine` objects are also awaitables, coroutines may be yielded from a coroutine, interrupting execution of the calling coroutine until the invoked coroutine completes. If the invoked coroutine executes successfully (fulfills), the final yielded value will be sent to the calling coroutine. If the invoked coroutine throws an exception (rejects), the exception will be thrown in the calling coroutine. This behavior is analogous to calling a synchronous function that either can return a value or throw an exception.

To make calling coroutines within other coroutines simpler, any generator yielded from a coroutine is automatically used to create a coroutine.

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Loop;

function add($value1, $value2) {
    yield $value1 + $value2;
}

function divide($value1, $value2) {
    if (0 === $value2) {
        throw new Exception('Division by zero!');
    }
    yield $value1 / $value2;
}

function calculate($value1, $value2) {
    $result  = (yield add($value1, $value2));
    $result += (yield divide($value1, $value2));
    yield $result;
}

$coroutine = new Coroutine(calculate(12, 0));

$coroutine->then(
    function ($result) {
        echo "Result: {$result}\n";
    },
    function (Exception $e) {
        echo "Error: {$e->getMessage()}\n";
    }
);

Loop\run();
```

## Coroutines in v1.x vs v2.x

Icicle v2.0 (master branch) (under development) takes advantage of two new features in PHP 7: generator delegation and generator return expressions.

Coroutines in v2.0 return values using the `return` keyword like a normal function. If a coroutine does not use `return` with an expression, the coroutine will resolve will `null`, just like a normal function that does not use `return` (or uses `return` without an expression). Requiring the use of `return` allows `yield from` to be used in coroutines, delegating to another coroutine without the overhead of creating and resolving a separate `Coroutine` object.

As an additional bonus, PHP 7 does not require parentheses around yield statements used as an expression (e.g.: `$result = (yield $awaitable);` can be written as `$result = yield $awaitable;` in PHP 7).

```php
// PHP 5.5+
$generator = function () {
    $value = (yield coroutineFunction());
    $result = (yield anotherCoroutineFunction($value));

    if (null === $result) {
        throw new Exception('Invalid value.'); // Rejects the coroutine.
    }

    yield $result; // Final yield is resolution value of the coroutine.
};

// PHP 7
$generator = function () {
    $value = yield from coroutineFunction(); // yield from avoids creating another Coroutine object.
    $result = yield from anotherCoroutineFunction($value);

    if (null === $result) {
        throw new Exception('Invalid value.'); // Rejects the coroutine.
    }

    return $result; // Uses return keyword to resolve the coroutine.
};
```

`yield` and `yield from` can be used directly with `return` in a coroutine:

- Returning the resolution value of an awaitable: `return yield $awaitable;`
- Delegating to another coroutine and returning the resolution value of that coroutine: `return yield from coroutineFunction();`


## Coroutine Docblock Annotations

Coroutines are labeled using the `@coroutine` docblock annotation. This indicates that the function or method returns a `\Generator` that is meant to be used to create a `Coroutine` instance or called within another coroutine using `yield` (or `yield from` in PHP 7).

For example, below is the definition of the function `Icicle\Coroutine\sleep()` including the docblock. This coroutine may be used to pause a coroutine for a given number of seconds. The coroutine is resolved with the number of seconds that were actually slept.

```php
/**
 * @coroutine
 *
 * @param float $time Time to sleep in seconds.
 *
 * @return \Generator
 *
 * @resolve float Actual time slept in seconds.
 */
function sleep($time): \Generator
{
    $start = yield Awaitable\resolve(microtime(true))->delay($time);

    yield microtime(true) - $start;
}
```

The function returns a `\Generator`, but the coroutine will eventually resolve to the value given in the `@resolve` annotation. If the coroutine could be rejected, this would be included in `@throws` annotations.
