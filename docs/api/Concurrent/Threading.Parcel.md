A thread-safe container that shares a value between multiple threads.

**Implements**
:   [`Sync\Parcel`](Sync.Parcel.md)

This parcel implementation is preferred when sharing objects between threads.


## __construct()

    new Parcel(mixed $value)

Creates a new shared object container.

### Parameters
`$value`
:   The value to store in the container.
