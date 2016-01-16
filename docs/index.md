**Icicle is a PHP library for writing *asynchronous* code using *synchronous* coding techniques.**

Icicle uses [coroutines] built with [awaitables] to facilitate writing asynchronous code using techniques normally used to write synchronous code, such as returning values and throwing exceptions. Writing functions in this way allows you to focus on the meaning of your code, without a mess of nested callbacks.


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


## Core Components
- [Event loop](api/Loop/index.md): The core of Icicle that manages scheduling incoming events and asynchronous functions.
- [Awaitables](api/Awaitable/index.md): Placeholders for future values of asynchronous operations. Callbacks registered with awaitables may return values and throw exceptions.
- [Coroutines](api/Coroutine/index.md): Interruptible functions for building asynchronous code using synchronous coding patterns and error handling.


## Optional Packages
- [Stream](api/Stream/index.md): Common coroutine-based interface for reading and writing data.
- [Socket](api/Socket/index.md): Asynchronous stream socket server and client.
- [Concurrent](api/Concurrent/index.md): Provides an easy to use interface for parallel execution with non-blocking communication and task execution.
- [DNS](api/Dns/index.md): Asynchronous DNS resolver and connector.
- [Filesystem](https://github.com/icicleio/filesystem): Asynchronous filesystem access *(under development)*.
- [HTTP](https://github.com/icicleio/http): Asynchronous HTTP server and client *(under development)*.
- [WebSocket](https://github.com/icicleio/websocket): Asynchronous WebSocket server and client *(under development)*.
- [React Adapter](manual/foreign-async-code.md): Adapts the event loop and awaitables of Icicle to interfaces compatible with components built for React.


## Getting help and support
If you're experiencing problems or find anything in this documentation that is confusing or misleading, you can find help and support in the following ways:

- Tweet us a question at the [official Twitter account](https://twitter.com/icicleio)
- Chat in the public [Gitter chat room](https://gitter.im/icicleio/icicle)
- Send us an email at [hello@icicle.io](mailto:hello@icicle.io)


## License
The Icicle library, all related packages, and documentation are licensed under the MIT license. View the [license file](https://github.com/icicleio/icicle/blob/master/LICENSE) for details.


[loop]:         manual/loop.md
[awaitables]:   manual/awaitables.md
[coroutines]:   manual/coroutines.md
[dns]:          api/dns.md
