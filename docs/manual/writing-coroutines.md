Coroutines are the basic units of execution in asynchronous code, and allow you to write expressive functions that return asynchronously without callbacks or boilerplate code.

Coroutines are created by functions returning a `Generator` that define interruption points using the `yield` keyword. When a coroutine yields a value, execution of the coroutine is temporarily interrupted, allowing other tasks to be run, such as I/O, timers, or other coroutines.


## Basic Coroutine

The code below creates a `Icicle\Coroutine\Coroutine` object from a basic generator that echoes the string "Hello world!". While this example is contrived, it demonstrates how the fulfillment value of a promise is used in a coroutine (see [Interrupting Coroutines with Promises](#interrupting-coroutines-with-promises)).

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Loop;
use Icicle\Promise;

$callback = function () {
    $data = (yield Promise\resolve("Hello, world!"));
    echo $data . "\n";
});

$coroutine = new Coroutine($callback());

Loop\run();
```


## Returning Values from Coroutines

The `return` keyword cannot be used in generators to return a value (note this will be changing in PHP 7 and will be used in the future to return values from coroutines). However, `return` can be used without an expression to halt execution of the generator. The return value (fulfillment value) of a coroutine is the last value yielded from the coroutine (or fulfillment value of a yielded promise). `return` may be used anytime to halt execution of a coroutine and fulfill with the last yielded value.

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


## Interrupting Coroutines with Promises

**When a coroutine yields a [promise](promises.md), execution of the coroutine is interrupted until the promise is resolved.**

Resolution of a yielded promise results in one of two actions within the coroutine:
1. If the promise is fulfilled, the statement that yielded the promise will take on the fulfillment value. For example, `$value = (yield Icicle\Promise\resolve(3.14159));` will set `$value` to `3.14159` when execution of the coroutine is resumed.
2. If the promise is rejected, the exception used to reject the promise will be thrown into the function at the yield statement. For example, `yield Icicle\Promise\reject(new Exception());` would behave identically to replacing the yield statement with `throw new Exception();`.

The example below yields a pending promise created by `Icicle\Promise\PromiseInterface::delay()` that is automatically resolved with the value given when creating the generator.

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Loop;
use Icicle\Promise;

$callback = function ($value) {
    $promise = Promise\resolve($value);

    // Delays coroutine execution for 1 second.
    yield $promise->delay(1);
};

$coroutine = new Coroutine($callback(3.14159));

$coroutine->then(function ($result) {
    echo "Result: {$result}\n";
});

Loop\run();
```

The above example is a simple contrived example to demonstrate how easy it is to write cooperative, asynchronous code using promises. The example below pipes data read from a readable stream into a writable stream, waiting for data to be available on the readable stream, then waiting until the data is successfully written to the writable stream before attempting to read more data from the readable stream.

```php
use Icicle\Coroutine;
use Icicle\Loop;
use Icicle\Socket\ReadableStreamInterface;
use Icicle\Socket\WritableStreamInterface;

// $readable is a ReadableStreamInterface instance.
// $writable is a WritableStreamInterface instance.

$coroutine = Coroutine\create(
    function (ReadableStreamInterface $readable, WritableStreamInterface $writable) {
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

Since `Coroutine` objects are also promises, coroutines may be yielded from a coroutine, interrupting execution of the calling coroutine until the invoked coroutine completes. If the invoked coroutine executes successfully (fulfills), the final yielded value will be sent to the calling coroutine. If the invoked coroutine throws an exception (rejects), the exception will be thrown in the calling coroutine. This behavior is analogous to calling a synchronous function that either can return a value or throw an exception.

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


## Coroutine Docblock Annotations

Coroutines are labeled using the `@coroutine` docblock annotation. This indicates that the function or method returns a `Generator` that is meant to be used to create a `Coroutine` instance or called within another coroutine using `yield` (or `yield from` in PHP 7).

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
    $start = yield Promise\resolve(microtime(true))->delay($time);

    yield microtime(true) - $start;
}
```

The function returns a `Generator`, but the coroutine will eventually resolve to the value given in the `@resolve` annotation. If the coroutine could be rejected, this would be included in `@throws` annotations.
