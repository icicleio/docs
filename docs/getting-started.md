We're glad you've chosen to learn about asynchronous PHP programming and how to use Icicle! The goal of the Icicle project is to make asynchronous coding efficient and much easier to write and understand.

Since you've gotten this far, let's get started with some basic concepts and some fun example code to demonstrate what Icicle has to offer.



## Example

The example below uses the [HTTP component](https://github.com/icicleio/http) (under development) to create a simple HTTP server that responds with `Hello, world!` to every request.

```php
#!/usr/bin/env php
<?php

require '/vendor/autoload.php';

use Icicle\Http\Message\RequestInterface;
use Icicle\Http\Message\Response;
use Icicle\Http\Server\Server;
use Icicle\Loop;

$server = new Server(function (RequestInterface $request) {
    $response = new Response(200);
    yield $response->getBody()->end('Hello, world!');
    yield $response->withHeader('Content-Type', 'text/plain');
});

$server->listen(8080);

echo "Server running at http://127.0.0.1:8080\n";

Loop\run();
```
