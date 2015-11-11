Before diving into using Icicle, it is important to understand the basic principles of asynchronous programming and non-blocking I/O. Asynchronous programming can be a difficult concept to understand and to apply to real code, so we will first discuss some important concepts about this kind of programming and how it relates to Icicle.

This is just a short tutorial on asynchronous programming and not a complete guide. There are many other excellent, more in-depth tutorials available online if you would like to learn more about the asynchronous model. If you're already familiar with asynchronous programming, it's probably OK to skip this chapter. You won't hurt our feelings if you do!



## The normal way

Normally, a program is written as a sequence of statements to be performed. Of course, these statements are ordered, and are always executed starting with the first statement and ending with the last. In the words of the King of Hearts,

> Begin at the beginning and go on till you come to the end: then stop.
>
> <cite>King of Hearts, in Lewis Carroll, *Alice’s Adventures in Wonderland*</cite>

This sequential system works rather well and is fairly logical and easy to understand. This works no matter what kinds of operations you are doing in your program; each operation simply happens one after the other, after the previous operation finishes.

Some operations involve sending or receiving data from external sources outside of the program. A common example is transmitting data through network sockets. Data being read from a socket is not always immediately available; it may take some time for the data to transfer depending on the network speed and other factors. These *I/O* operations are *blocking* if, when the operation is called, the operation does not return until it is finished.

Let's consider some simple PHP code:

```php
// Open a socket stream.
$socket = fsockopen('8.8.8.8', 80);

// Read 512 bytes of data.
$message = fread($socket, 512);

echo "Message: " . $message;

// Close the socket.
fclose($socket);
```

`fread()` here is a blocking call that reads incoming data from a socket. Because the call is blocking, the `echo` statement will not be executed until the file reading operation finishes reading 512 bytes. Programming in this way is called *synchronous*, because it uses a sequence of blocking calls.



## Non-blocking operations

Now blocking I/O isn't always bad thing. If there's nothing else you need your program to do in the meantime, then blocking I/O will work just fine. But if you plan on handling multiple operations simultaneously, then this method of I/O won't work well. If you write a single-threaded network server this way, you will quickly notice that the program can only handle one connection at a time.

The solution to this problem is to use something called *non-blocking* operations. An operation is non-blocking if calling the operation does *not* block the program from continuing, and instead returns before the operation is complete. Most operating systems let us call I/O operations in a non-blocking way. In PHP, we can enable non-blocking reads and writes on a stream with the `stream_set_blocking()` function:

```php
// Open a socket stream.
$socket = fsockopen('8.8.8.8', 80);

// Enable non-blocking I/O calls.
stream_set_blocking($socket, 0);

// Returns immediately, whether 512 bytes were available or not.
$message = fread($socket, 512);

echo "Message: " . $message;

// Close the socket.
fclose($socket);
```

After enabling non-blocking mode, calling `fread()` on the socket stream will no longer block the program if data is not yet available. Now we have another problem: how do we know when the data *is* available? We successfully avoided blocking the program, but we still don't have all of the data we need. It is very likely that `$message` not have the full 512 byte message we wanted. In fact, it may very well be empty. We could check repeatedly if the data has all been read with a `while` loop, but then the loop will block the program until the data is read and we're back to where we started.

In order to make this technique work, we need to first wait for the data to be available before printing out the message. On most systems, we can use another function `stream_select()`, which is a wrapper around the `select()` system call. `select()`'s job is to examine an array of streams and notify us when new data is available or when a stream becomes writable. We can rewrite our program to use this function:

```php
// Open a socket stream.
$socket = fsockopen('8.8.8.8', 80);

// Enable non-blocking I/O calls.
stream_set_blocking($socket, 0);

// We will fill up the message as data becomes available.
$message = "";

// Loop while there is more data expected.
while (strlen($message) < 512) {
    // Put the socket into the arrays for each type of event we're interested in.
    $read = [$socket];
    $write = [];
    $except = [];

    // Wait infinitely (0 seconds means forever) for an event to happen.
    if (stream_select($read, $write, $except, 0) > 0) {
        // An event happened and the only thing we registered interest in is new data to read for $socket.
        // Now read until all the new data was taken.
        $chunk = "";
        do {
            $chunk = fread($socket, 512 - strlen($message));
            $message .= $chunk;
        } while ($chunk !== "" && strlen($message) < 512);
    }
}

echo "Message: " . $message;

// Close the socket.
fclose($socket);
```

Our program became much more complicated to just read from a single socket, but using `stream_select()` suddenly allows us to handle reading and writing many different streams at once, without one operation blocking the other. This is just one of the possible benefits of using non-blocking I/O.

It may be interesting to note that `stream_select()` itself is actually a blocking operation. When called, it does not return until a stream event actually occurs. When combining all of the events we're waiting for, it is a good thing to block if the program has nothing else to do, since it lets the operating system give the program process's CPU time to another process instead since we don't need it. Otherwise we would just be wasting CPU cycles in order to idly twiddle our thumbs.



## Asynchronous programming flow

In the last example we can see that writing non-blocking code can get complicated very quickly. To avoid this problem, we need to make it easier to get notified of the completion of a non-blocking operation. Languages and libraries that implement non-blocking operations like above provide abstractions that make non-blocking code much simpler to write and understand. One approach is to use callback functions that will be invoked after the operation finishes. This is a common approach, and is the basic building block for forming other ways of handling control flow.

A callback-based API might look something like this (note that `freadAsync()` isn't an actual function):

```php
// Open a socket stream.
$socket = fsockopen('8.8.8.8', 80);

// Read 512 bytes of data, without blocking.
freadAsync($socket, 512, function ($socket, $message) {
    // This callback function is invoked when
    echo "Message: " . $message;

    // Close the socket.
    fclose($socket);
});

// At some point, we need to run a loop that checks for pending operations and invokes callbacks when ready.
doAsyncLoop();
```

In this example, we're using callbacks to handle the result of a non-blocking operation. The callback will be invoked "sometime later", when the operation finishes. Suddenly, our program no longer appears to simply run from top to bottom -- the call to `doAsyncLoop()` will happen **before** we `echo` the message. We are no longer programming synchronously, but *asynchronously*. This is what asynchronous programming is about: handling multiple operations simultaneously and reacting to the operations as they complete.

Now, callbacks can get out of hand quickly if you need to do many things one after the other, so multiple alternative solutions have been developed by many different people. The primary approach Icicle uses is *coroutines*, which we will discuss in a later chapter.



## Event loops

Our previous chunk of code using `stream_select()` may be non-blocking, but it might block unrelated code elsewhere from doing the same thing. Something else we need is to make sure all of our operations are handled in the same loop, so that we can be aware of all incoming events. This loop is actually what the *event loop* does in asynchronous libraries. The event loop acts as a central location for registering interest in an event and then waiting for any of the events to occur.

One of the core pieces of Icicle is an event loop that handles all of these events. In the next chapter, we will explore the Icicle event loop in further depth.
