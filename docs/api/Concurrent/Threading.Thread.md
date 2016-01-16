An execution context using native multi-threading. Implements [`Icicle\Concurrent\Context`](#contextinterface).

The thread context is not itself threaded. A local instance of the context is maintained both in the context that creates the thread and in the thread itself.

### Thread::spawn()

```php
Thread::spawn(
    callable(...$args): mixed $function,
    ...$args
): Thread
```

Creates a new thread and immediately starts it. All arguments following the function to invoke in the thread will be copied and passed as parameters to the function to invoke.

#### Parameters
`callable(...$args): mixed $function`
:   The function to invoke inside the new thread.

`mixed ...$args`
:   Arguments to pass to `$function`.

!!! warning
    Due to the underlying process of passing a closure to another thread, using a closure for `$function` that [imports variables](http://php.net/manual/en/functions.anonymous.php#example-195) from a scope in the parent thread can cause malformed internal pointers. Attempting to pass such a function will result in an `Icicle\Exception\InvalidArgumentError` being thrown.

Example:

```php
$thread = Thread::spawn(function ($value) {
    echo $value === 42 ? 'true' : 'false';
}, 42);
```
