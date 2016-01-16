Interface for sending messages between execution contexts. A `Icicle\Concurrent\Sync\Channel` object both acts as a sender and a receiver of messages.

## send()

    Channel::send(mixed $data): \Generator

Sends a value across the channel to the receiver.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`mixed $data`
:   The data to send to the receiver. The value given must be serializable.

### Resolution value
`int`
:   The number of bytes written to the stream to send the value.

## receive()

    Channel::receive(): \Generator

Receives the next pending value in the channel from the sender. Resolves with the received value.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Resolution value
`mixed`
:   The data received.
