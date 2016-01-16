A parcel object for sharing data across execution contexts.

A parcel is an object that stores a value in a safe way that can be shared between different threads or processes. Different handles to the same parcel will access the same data, and a parcel handle itself is serializable and can be transported to other execution contexts.

Wrapping and unwrapping values in the parcel are not atomic. To prevent race conditions and guarantee safety, you should use the provided synchronization methods to acquire a lock for exclusive access to the parcel first before accessing the contained value.

When a parcel is cloned, a new parcel is created and the original parcel's value is duplicated and copied to the new parcel.

### unwrap()

    Parcel::unwrap(): mixed

Unwraps the parcel and returns the value inside the parcel.

### synchronized()

    Parcel::synchronized(callable(mixed $value): mixed $function): \Generator

Calls the given callback function while maintaining a lock on the parcel so only one thread may modify the value of the parcel. The current value of the parcel is given to the callback function and the function should return the new value to be stored in the parcel.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

#### Parameters
`callable(mixed $value): mixed $function`
:   The callback function to be invoked. This function is given the current parcel value as the parameter and should return the new value to store in the parcel.

#### Resolve
`mixed`
:   The new parcel value.
