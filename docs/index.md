**Icicle is a PHP library for writing *asynchronous* code using *synchronous* coding techniques.**

Icicle uses [Coroutines] built with [Promises] to facilitate writing asynchronous code using techniques normally used to write synchronous code, such as returning values and throwing exceptions, instead of using nested callbacks typically found in asynchronous code.

## Library Components

- [Coroutines]: Interruptible functions for building asynchronous code using synchronous coding patterns and error handling.
- [Promises]: Placeholders for future values of asynchronous operations. Callbacks registered with promises may return values and throw exceptions.
- [Loop (event loop)][loop]: Used to schedule functions, run timers, handle signals, and poll sockets for pending data or await for space to write.

## Available Components

- [Stream](https://github.com/icicleio/stream): Common coroutine-based interface for reading and writing data.
- [Socket](https://github.com/icicleio/socket): Asynchronous stream socket server and client.
- [Concurrent](https://github.com/icicleio/concurrent): Provides an easy to use interface for parallel execution with non-blocking communication and task execution (under development).
- [DNS](https://github.com/icicleio/dns): Asynchronous DNS resolver and connector.
- [HTTP](https://github.com/icicleio/http): Asynchronous HTTP server and client (under development).
- [React Adapter](https://github.com/icicleio/react-adaptor): Adapts the event loop and promises of Icicle to interfaces compatible with components built for React.

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

## Documentation and Support

- [Full API Documentation](https://github.com/icicleio/icicle/wiki)
- [Official Twitter](https://twitter.com/icicleio)
- [Gitter Chat](https://gitter.im/icicleio/icicle)

## Function prototypes

Prototypes for object instance methods are described in this documentation using the following syntax:

```php
ClassOrInterfaceName::methodName(ArgumentType $arg): ReturnType
```

Prototypes for static object methods are described in this documentation using the following syntax:

```php
static ClassOrInterfaceName::methodName(ArgumentType $arg): ReturnType
```

Prototypes for functions in a namespace are described in this documentation using the following syntax:

```php
Namespace\functionName(ArgumentType $arg): ReturnType
```

To document the expected prototype of a callback function used as method arguments or return types, this documentation uses the following syntax for `callable` types:

```php
callable<(ArgumentType $arg): ReturnType>
```

## License
The Icicle library, all related packages, and documentation are licensed under the MIT license. View the [license file](https://github.com/icicleio/icicle/blob/master/LICENSE) for details.


[loop]:         manual/loop.md
[promises]:     manual/promises.md
[coroutines]:   manual/coroutines.md
[dns]:          api/dns.md
