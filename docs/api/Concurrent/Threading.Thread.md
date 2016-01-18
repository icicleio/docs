An execution context using native multi-threading.

**Implements**
:   [`Strand`](Strand.md)


## enabled()

    static Thread::enabled(): bool

Checks if threading is enabled.

### Return value
True if threading is enabled, otherwise false.


## spawn()

    static Thread::spawn(
        callable(...$args): mixed $function,
        ...$args
    ): Thread

Creates a new thread and immediately starts it. All arguments following the function to invoke in the thread will be copied and passed as parameters to the function to invoke.

### Parameters
`callable(...$args): mixed $function`
:   The function to invoke inside the new thread.

`mixed ...$args`
:   Arguments to pass to `$function`.

### Throws
`Icicle\Exception\InvalidArgumentError`
:   If the given function cannot be safely invoked in a thread.

`Icicle\Exception\UnsupportedError`
:   Thrown if the pthreads extension is not available.

!!! warning
    Due to the underlying process of passing a closure to another thread, using a closure for `$function` that [imports variables](http://php.net/manual/en/functions.anonymous.php#example-195) from a scope in the parent thread can cause malformed internal pointers. Attempting to pass such a function will result in an `Icicle\Exception\InvalidArgumentError` being thrown.

### Example
```php
$thread = Thread::spawn(function ($value) {
    echo $value === 42 ? 'true' : 'false';
}, 42);
```


## __construct()

    new Thread(
        callable(...$args): mixed $function,
        ...$args
    )

Creates a new thread. The thread will not be run until you call [`start()`](Context.md#start).

### Parameters
`callable(...$args): mixed $function`
:   The function to invoke inside the new thread.

`mixed ...$args`
:   Arguments to pass to `$function`.

### Throws
`Icicle\Exception\InvalidArgumentError`
:   If the given function cannot be safely invoked in a thread.

`Icicle\Exception\UnsupportedError`
:   Thrown if the pthreads extension is not available.
