The first step towards using Icicle is installing it! Fortunately, Icicle is easy to install and requires no PHP extensions for the core functionality to work.

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



## Minimum PHP version

The current version requires a PHP version of 5.5 or higher, though Icicle has multiple versions in progress that track different PHP versions. You can check what version of PHP is required for a release of Icicle with Composer:

```bash
composer show icicleio/icicle \^0.8
```



## Suggested extensions

Some extensions enable Icicle to provide additional features.

- [pcntl extension](http://php.net/manual/en/book.pcntl.php): Enables custom process signal handling.
- [ev extension](https://pecl.php.net/package/ev): Allows for the most performant event loop implementation.
- [event extension](https://pecl.php.net/package/event): Another extension allowing for event loop implementation with better performance (ev extension preferred).
- [libevent extension](https://pecl.php.net/package/libevent): Similar to the event extension, it allows for event loop implementation with better performance (ev extension preferred).



## Optional packages

To allow for the greatest flexibility possible, Icicle is split up into several packages. The core package is [`icicleio/icicle`](https://packagist.org/packages/icicleio/icicle) and provides everything you need to start writing asynchronous code. Other features like sockets, threads, and HTTP parsing are available as separate optional packages. Here is a list of the primary official packages:

- [`icicleio/stream`](https://packagist.org/packages/icicleio/stream): Base package for Icicle's I/O streams API. Provides pipe streams and in-memory streams, along with helpers for working with streams.
- [`icicleio/socket`](https://packagist.org/packages/icicleio/socket): Provides TCP and UDP sockets using a stream interface.
- [`icicleio/dns`](https://packagist.org/packages/icicleio/dns): Provides a native implementation of a DNS client.
- [`icicleio/concurrent`](https://packagist.org/packages/icicleio/concurrent): Provides all of Icicle's concurrency features, such as threads, forks, shared memory, semaphores, and workers.
- [`icicleio/http`](https://packagist.org/packages/icicleio/http): Provides a stream-aware HTTP parser, with an HTTP client and HTTP server.
- [`icicleio/react-adapter`](https://packagist.org/packages/icicleio/react-adapter): Adapts the event loop and promises of Icicle to interfaces compatible with components built for ReactPHP.

Installing these extra packages is easy and is also done using Composer.

The documentation for all of these optional packages are included here, so be aware that many parts of the manual are written assuming you have or want some of these packages.



## Supported platforms

The core of Icicle does not depend on any platform-specific code and can run anywhere a PHP interpreter will run. Some features, such as process signals or process forking, require certain platform features; these extra features may not be available on all platforms. Check the documentation for specific features you want to use to see if there are any platform restrictions.
