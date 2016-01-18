All stream resource (pipe) classes in this package (and some other packages suck as [socket](https://github.com/icicleio/socket)) implement `Icicle\Stream\Resource`.


## getResource()

    Resource::getResource(): resource

### Return value
`resource`
:   Returns the underlying PHP stream resource.


## isOpen()

    Resource::isOpen(): bool

### Return value
`bool`
:   `true` if the resource is still open, `false` if not.


## close()

    Resource::close(): void

Closes the stream resource, making it unreadable or unwritable.
