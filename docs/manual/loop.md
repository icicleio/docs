The event loop schedules functions, runs timers, handles signals, and polls sockets for pending reads and available writes. There are several event loop implementations available depending on what PHP extensions are available. The `Icicle\Loop\SelectLoop` class uses only core PHP functions, so it will work on any PHP installation, but is not as performant as some of the other available implementations. All event loops implement `Icicle\Loop\Loop` and provide the same features.

The event loop should be accessed via functions defined in the `Icicle\Loop` namespace. If a program requires a specific or custom event loop implementation, `Icicle\Loop\loop()` can be called with an instance of `Icicle\Loop\Loop` before any other loop functions to use that instance as the event loop.

The `Icicle\Loop\run()` function runs the event loop and will not return until the event loop is stopped or no events are pending in the loop.

The following code demonstrates how timers can be created to execute functions after a number of seconds elapses using the `Icicle\Loop\timer()` function.

```php
use Icicle\Loop;

Loop\timer(1, function () { // Executed after 1 second.
	echo "First.\n";
	Loop\timer(1.5, function () { // Executed after 1.5 seconds.
	    echo "Second.\n";
	});
	echo "Third.\n";
	Loop\timer(0.5, function () { // Executed after 0.5 seconds.
		echo "Fourth.\n";
	});
	echo "Fifth.\n";
});

echo "Starting event loop.\n";
Loop\run();
```

The above code will output:

```
Starting event loop.
First.
Third.
Fifth.
Fourth.
Second.
```

## Loop implementations

There are currently three loop classes, each implementing `Icicle\Loop\Loop`. Any custom implementation written must also implement this interface. Custom loop implementations can be used as the active event loop using the [`Icicle\Loop\loop()`](../api/Loop/index.md#loop) function.

- `Icicle\Loop\SelectLoop`: Works with any installation of PHP since it relies only on core functions. Uses `stream_select()` or `usleep()` depending on the events pending in the loop.
- `Icicle\Loop\EvLoop`: Uses libev to create an event loop and requires the [`ev` pecl extension](https://pecl.php.net/package/ev). Preferred implementation for best performance.
- `Icicle\Loop\UvLoop`: Uses libuv to create an event loop and requires the [`uv` extension](https://github.com/bwoebi/php-uv). PHP 7 only (experimental).

While each implementation is different, there should be no difference in the behavior of a program based on the loop implementation used. Note that there may be some differences in the exact timing of the execution of certain events or the order in which different types of events are executed (particularly the ordering of timers and signals). However, programs should not be reliant on the exact timing of callback function execution and therefore should not be affected by these differences. Regardless of implementation, callbacks scheduled with `queue()` are always executed in the order queued.


## Throwing exceptions

Functions queued using `Icicle\Loop\queue()` or callback functions used for timers, immediates, signals, and IO watchers should not throw exceptions. If one of these functions throws an exception, it will be thrown from the `Icicle\Loop\run()` function. These are referred to as **uncatchable exceptions** since there is no way to catch the thrown exception within the event loop. If an exception can be thrown from code within a callback, that code should be surrounded by a try/catch block and the exception handled within the callback.


## Events

When an event is scheduled in the event loop through the methods `poll()`, `await()`, `timer()`, `periodic()`, and `immediate()`, an object extending `Icicle\Loop\Watcher\Watcher` is returned. These objects provide methods for listening, cancelling, or determining if the event is pending.
