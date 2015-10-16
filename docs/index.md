**Icicle is a PHP library for writing *asynchronous* code using *synchronous* coding techniques.**

Icicle uses [Coroutines](#coroutines) built with [Promises](#promises) to facilitate writing asynchronous code using techniques normally used to write synchronous code, such as returning values and throwing exceptions, instead of using nested callbacks typically found in asynchronous code.

## Library Components

- [Coroutines](#coroutines): Interruptible functions for building asynchronous code using synchronous coding patterns and error handling.
- [Promises](#promises): Placeholders for future values of asynchronous operations. Callbacks registered with promises may return values and throw exceptions.
- [Loop (event loop)](#loop): Used to schedule functions, run timers, handle signals, and poll sockets for pending data or await for space to write.

## Available Components

- [Stream](https://github.com/icicleio/stream): Common coroutine-based interface for reading and writing data.
- [Socket](https://github.com/icicleio/socket): Asynchronous stream socket server and client.
- [Concurrent](https://github.com/icicleio/concurrent): Provides an easy to use interface for parallel execution with non-blocking communication and task execution (under development).
- [DNS](https://github.com/icicleio/dns): Asynchronous DNS resolver and connector.
- [HTTP](https://github.com/icicleio/http): Asynchronous HTTP server and client (under development).
- [React Adapter](https://github.com/icicleio/react-adaptor): Adapts the event loop and promises of Icicle to interfaces compatible with components built for React.

### Requirements

- PHP 5.5+

### Installation

The recommended way to install Icicle is with the [Composer](http://getcomposer.org/) package manager. (See the [Composer installation guide](https://getcomposer.org/doc/00-intro.md) for information on installing and using Composer.)

Run the following command to use Icicle in your project:

```bash
composer require icicleio/icicle
```

You can also manually edit `composer.json` to add Icicle as a project requirement.

```js
// composer.json
{
    "require": {
        "icicleio/icicle": "^0.8"
    }
}
```

### Suggested

- [pcntl extension](http://php.net/manual/en/book.pcntl.php): Enables custom signal handling.
- [ev extension](https://pecl.php.net/package/ev): Allows for the most performant event loop implementation.
- [event extension](https://pecl.php.net/package/event): Another extension allowing for event loop implementation with better performance (ev extension preferred).
- [libevent extension](https://pecl.php.net/package/libevent): Similar to the event extension, it allows for event loop implementation with better performance (ev extension preferred).

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

Prototypes for functions in a namespace are described in this documentation using the following syntax:

```php
Namespace\functionName(ArgumentType $arg): ReturnType
```

To document the expected prototype of a callback function used as method arguments or return types, this documentation uses the following syntax for `callable` types:

```php
callable<(ArgumentType $arg): ReturnType>
```
