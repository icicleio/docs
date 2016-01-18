A coroutine-based interface for creating a UDP listener and sender.


## receive()

    Datagram::receive(int $length, float $timeout): \Generator

A coroutine that is fulfilled with an array when a data is received on the UDP socket (datagram). The array is a 0-indexed array containing the IP address, port, and data received, in that order.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`int $length = 0`
:   The maximum number of bytes to receive. Use `0` for an unlimited amount.

`float $timeout = 0`
:   Number of seconds until the returned awaitable is rejected with a `TimeoutException` and the stream is closed if the data cannot be written to the stream. Use `0` for no timeout.

### Resolution value
`[string, int, string]`
:   An array with three elements: The IP address of the data sender, the port of the data sender, and the data.


## send()

    Datagram::send(
        string $address,
        int $port,
        string $data
    ): \Generator

Send the given data to the IP address and port. This coroutine is fulfilled with the amount of data sent once the data has successfully been sent.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### Parameters
`string $address`
:   IP address of receiver.

`int $port`
:   Port of receiver.

`string $data`
:   Data to send.

### Resolution value
`int`
:   Length of sent data.


## getAddress()

    Datagram::getAddress(): string

### Return value
`string`
:   Returns the local IP address as a string.

### getPort()

    Datagram::getPort(): int

### Return value
`int`
:   Returns the local port.
