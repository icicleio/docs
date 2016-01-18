All stream interfaces extend this basic interface.


## isOpen()

    Stream::isOpen(): bool

### Return value
`bool`
:   `true` if the stream is still open, `false` if not.


## close()

    Stream::close(): void

Closes the stream. Once closed, a stream will no longer be readable or writable.
