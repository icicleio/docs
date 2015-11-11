Traditional console applications can benefit from asynchronous I/O as well as web and networking programs. Imagine you need to create an interactive console program that allows you to download files as you enter their URLs in the console. You might write code such a program like this:

```php
use Icicle\Coroutine;
use Icicle\Http;
use Icicle\Loop;
use Icicle\Stream;

Coroutine\create(function () {
    // Infinitely get input from the console until the user terminates the program.
    while (true) {
        // Read a line of text from standard input.
        // The program will block here, waiting for another line of input.
        $line = fgets(STDIN);

        // Trim extra newlines and whitespace.
        $url = trim($line);

        // Fetch the file at the given URL with an HTTP client.
        // We use a new coroutine here so that the download will run independently
        // of our loop.
        Coroutine\create(function () use ($url) {
            $client = new Http\Client();
            $response = (yield $client->request('GET', $url));

            // Collect the response body and write it to a file.
            $contents = yield Stream\readAll($response->getBody());
            $file = basename($url);
            file_put_contents($file, $contents);
        });
    }
});

// Run the event loop, as usual.
Loop\run();
```

The most interesting bit of this code is `fgets(STDIN)`. Since [`fgets()`](http://php.net/fgets) is a synchronous function, the entire program blocks until it can read a whole line from the console. Programmers deal with this all the time, but we can write a better solution. What we *really* want is to allow the user to continuously type in URLs as the files are downloaded in the background. To do this, we need to read from standard input asynchronously, which we can do with pipes.

Just like sockets, pipes can also be read from and written to asynchronously. Icicle provides some predefined functions that we can use to get streams for standard pipes:

- `Icicle\Stream\stdin()`: Gets a readable stream for standard input.
- `Icicle\Stream\stdout()`: Gets a writable stream for standard output.
- `Icicle\Stream\stderr()`: Gets a writable stream for standard error output.

Rewriting our example with these functions in hand, we can now read from the console without freezing the downloads in the background:

```php
use Icicle\Coroutine;
use Icicle\Http;
use Icicle\Loop;
use Icicle\Stream;
use Icicle\Stream\Text\TextReader;

Coroutine\create(function () {
    // Get an asynchronous pipe for standard input and wrap it in a text reader.
    $stdin = new TextReader(Stream\stdin());

    // Infinitely get input from the console until the user terminates the program.
    while (true) {
        // Read a line of text from standard input, using the asynchronous pipe.
        // The program will not be blocked.
        $line = (yield $stdin->readLine());

        // Trim extra newlines and whitespace.
        $url = trim($line);

        // Fetch the file at the given URL with an HTTP client.
        // We use a new coroutine here so that the download will run independently
        // of our loop.
        Coroutine\create(function () use ($url) {
            $client = new Http\Client();
            $response = (yield $client->request('GET', $url));

            // Collect the response body and write it to a file.
            $contents = yield Stream\readAll($response->getBody());
            $file = basename($url);
            file_put_contents($file, $contents);
        });
    }
});

// Run the event loop, as usual.
Loop\run();
```

Instead of using `fgets()` and blocking the program, we wrap `stdin()` in a `TextReader` and then asynchronously read a line of input. Since reading a line will no longer block the program, our downloads will be free to continue in the background while we wait for the user to type in another URL.
