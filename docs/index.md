**Icicle is a PHP library for writing *asynchronous* code using *synchronous* coding techniques.**

Icicle uses [coroutines] built with [promises] to facilitate writing asynchronous code using techniques normally used to write synchronous code, such as returning values and throwing exceptions. Writing functions in this way allows you to focus on the meaning of your code, without a mess of nested callbacks.


## Features
- Full-featured event loop for asynchronous programming
- Multiple event loop backends
- Asynchronous TCP and UDP sockets
- Asynchronous DNS resolution
- Standalone HTTP server
- Multi-processing using forking or child processes
- Multi-threading using native threads
- Asynchronous process signal handling
- Worker pool for running blocking tasks
- Non-blocking concurrency primitives


## Core components
- [Event loop](api/loop.md): The core of Icicle that manages scheduling incoming events and asynchronous functions.
- [Promises](api/promise.md): Placeholders for future values of asynchronous operations. Callbacks registered with promises may return values and throw exceptions.
- [Coroutines](api/coroutine.md): Interruptible functions for building asynchronous code using synchronous coding patterns and error handling.


## Optional packages
- [Stream](https://github.com/icicleio/stream): Common coroutine-based interface for reading and writing data.
- [Socket](https://github.com/icicleio/socket): Asynchronous stream socket server and client.
- [Concurrent](https://github.com/icicleio/concurrent): Provides an easy to use interface for parallel execution with non-blocking communication and task execution (under development).
- [DNS](https://github.com/icicleio/dns): Asynchronous DNS resolver and connector.
- [HTTP](https://github.com/icicleio/http): Asynchronous HTTP server and client (under development).
- [React Adapter](https://github.com/icicleio/react-adaptor): Adapts the event loop and promises of Icicle to interfaces compatible with components built for React.


## Getting help and support
If you're experiencing problems or find anything in this documentation that is confusing or misleading, you can find help and support in the following ways:

- Tweet us a question at the [official Twitter account](https://twitter.com/icicleio)
- Chat in the public [Gitter chat room](https://gitter.im/icicleio/icicle)
- Send us an email at [hello@icicle.io](mailto:hello@icicle.io)


## License
The Icicle library, all related packages, and documentation are licensed under the MIT license. View the [license file](https://github.com/icicleio/icicle/blob/master/LICENSE) for details.


[loop]:         manual/loop.md
[promises]:     manual/promises.md
[coroutines]:   manual/coroutines.md
[dns]:          api/dns.md
