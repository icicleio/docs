**[Loop API documentation](api/loop.md)**

The event loop schedules functions, runs timers, handles signals, and polls sockets for pending reads and available writes. There are several event loop implementations available depending on what PHP extensions are available. The `Icicle\Loop\SelectLoop` class uses only core PHP functions, so it will work on any PHP installation, but is not as performant as some of the other available implementations. All event loops implement `Icicle\Loop\LoopInterface` and provide the same features.

The event loop should be accessed via functions defined in the `Icicle\Loop` namespace. If a program requires a specific or custom event loop implementation, `Icicle\Loop\loop()` can be called with an instance of `Icicle\Loop\LoopInterface` before any other loop functions to use that instance as the event loop.

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
