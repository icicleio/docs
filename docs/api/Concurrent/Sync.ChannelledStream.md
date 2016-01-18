A channel implementation that uses a pair of socket streams.

**Implements**
:   [`Sync\Channel`](Sync.Channel.md)


## __construct()

    new ChannelledStream(
        DuplexStream|ReadableStream $read,
        WritableStream|null $write = null
    )

Creates a new channel instance from one or two streams. Either a single [`DuplexStream`](../Stream/DuplexStream.md) stream can be given, or a separate [`ReadableStream`](../Stream/ReadableStream.md) stream and [`WritableStream`](../Stream/WritableStream.md) stream can be used.

### Parameters
`Icicle\Stream\DuplexStream|Icicle\Stream\ReadableStream $read`
:   The single duplex stream instance or the readable stream to use for the channel.

`Icicle\Stream\WritableStream|null $write`
:   The writable stream to use for the channel if `$read` was only a readable stream.

### Throws
`Icicle\Exception\InvalidArgumentError`
:   Thrown if no write stream is provided and the read stream is not a duplex stream.


## isOpen()

    ChannelledStream::isOpen(): bool

Determines if the channel is open.


## close()

    ChannelledStream::close()

Closes the channel.
