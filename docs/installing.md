## Prerequisites

- PHP 5.5+

## Installation

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

## Suggested extensions

- [pcntl extension](http://php.net/manual/en/book.pcntl.php): Enables custom signal handling.
- [ev extension](https://pecl.php.net/package/ev): Allows for the most performant event loop implementation.
- [event extension](https://pecl.php.net/package/event): Another extension allowing for event loop implementation with better performance (ev extension preferred).
- [libevent extension](https://pecl.php.net/package/libevent): Similar to the event extension, it allows for event loop implementation with better performance (ev extension preferred).
