Coroutines are interruptible functions implemented using [Generators](http://www.php.net/manual/en/language.generators.overview.php). A `Generator` usually uses the `yield` keyword to yield a value from a set to implement an iterator. Coroutines use the `yield` keyword to define interruption points. When a coroutine yields a value, execution of the coroutine is temporarily interrupted, allowing other tasks to be run, such as I/O, timers, or other coroutines.

When a coroutine yields a [promise](promises.md), execution of the coroutine is interrupted until the promise is resolved. If the promise is fulfilled with a value, the yield statement that yielded the promise will take on the resolved value. For example, `$value = (yield Icicle\Promise\resolve(2.718));` will set `$value` to `2.718` when execution of the coroutine is resumed. If the promise is rejected, the exception used to reject the promise will be thrown into the function at the yield statement. For example, `yield Icicle\Promise\reject(new Exception());` would behave identically to replacing the yield statement with `throw new Exception();`.

Note that **no callbacks need to be registered** with the promises yielded in a coroutine and **errors are reported using thrown exceptions**, which will bubble up to the calling context if uncaught in the same way exceptions bubble up in synchronous code.

**`Icicle\Coroutine\Coroutine` instances are also [promises](promises.md), implementing `Icicle\Promise\PromiseInterface`.** The coroutine is fulfilled with the last value yielded from the generator (or fulfillment value of the last yielded promise) or rejected if an exception is thrown from the generator. A coroutine may then yield other coroutines, suspending execution until the yielded coroutine has resolved. If a coroutine yields a `Generator`, it will automatically be converted to a `Coroutine` and handled in the same way as a yielded coroutine.

## Writing Generators as Coroutines

Coroutines are created by functions returning a `Generator` that define interruption points using the `yield` keyword. When a coroutine yields a value, execution of the coroutine is temporarily interrupted, allowing other tasks to be run, such as I/O, timers, or other coroutines.

### Basic Coroutine

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

### Returning Values from Coroutines

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

### Throwing Exceptions from Coroutines

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

### Interrupting Coroutines with Promises

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

### Calling Coroutines Within Another Coroutine

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

## Coroutines as Promises

`Icicle\Coroutine\Coroutine` implements `Icicle\Coroutine\CoroutineInterface`, which extends `Icicle\Promise\PromiseInterface`. **Any methods available on promises are also available on coroutines and a coroutine can be treated just like any other promise.**

See the [Promise API documentation](promises.md) for the complete list of the methods available in `Icicle\Promise\PromiseInterface` and the other methods available for working with promises.

## Creating Coroutines

A `Coroutine` instance can be created in a few different ways depending on your needs.

### Coroutine Constructor

```php
$coroutine = new Coroutine(Generator $generator)
```

As shown in the examples above, a `Icicle\Coroutine\Coroutine` instance can be created by passing a `Generator` to the constructor. Execution of the coroutine is begun asynchronously, after leaving the calling scope of the constructor (e.g. after the function calling the constructor returns).

---

### Coroutine\wrap()

```php
Coroutine\wrap(
    callable<(mixed ...$args): Generator> $callback
): callable<(mixed ...$args): CoroutineInterface>
```

Returns a `callable` that returns a `Icicle\Coroutine\Coroutine` by calling `$callback` that must return a `Generator` written to be a coroutine. Any arguments given to the returned callable are also passed to `$callback`.

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

---

### Coroutine\create()

```php
Coroutine\create(
    callable<(mixed ...$args): Generator> $callback,
    mixed ...$args
): CoroutineInterface
```

Calls the given callback function with the provided arguments. The callback function should return a `Generator` written to be a coroutine.

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

`Icicle\Coroutine\Coroutine` objects have some methods for controlling execution once they are created.

#### pause()

```php
CoroutineInterface::pause(): void
```

Pauses the coroutine once it reaches a `yield` statement (if executing). If the coroutine was already at a `yield` statement (or has not begun execution), no further code will be executed until resumed with `resume()`. Any promises that the coroutine is currently waiting for will continue to do work to be resolved, but once resolved, the coroutine will not continue until resumed.

---

#### resume()

```php
CoroutineInterface::resume(): void
```

Resumes the coroutine if it was paused. If the coroutine was waiting for a promise to resolve, the coroutine will not continue execution until the promise has resolved.

---

#### isPaused()

```php
CoroutineInterface::isPaused(): bool
```

Determines if the coroutine is currently paused. Note that true is only returned if the coroutine was explicitly paused. It does not return true if the coroutine is waiting for a promise to resolve.

---

#### cancel()

```php
CoroutineInterface::cancel(mixed $reason = null): void
```

Cancels execution of the coroutine. If the coroutine is waiting on a promise, that promise is cancelled with the given exception. If no exception is given, an instance of `Icicle\Promise\Exception\CancelledException` will be used.
