A container object for sharing a value across contexts.

**Implements**
:   [`Sync\Parcel`](Sync.Parcel.md)
:   [`Serializable`](http://php.net/Serializable)

A shared object is a container that stores an object inside shared memory. The object can be accessed and mutated by any thread or process. The shared object handle itself is serializable and can be sent to any thread or procss to give access to the value that is shared in the container.

Because each shared object uses its own shared memory segment, it is much more efficient to store a larger object containing many values inside a single shared container than to use many small shared containers.

When used with forking, the object must be created prior to forking for both processes to access the synchronized object.

Requires the [shmop](http://php.net/manual/en/book.shmop.php) extension to be enabled.

!!! note
    Accessing a shared object is not atomic. Access to a shared object should be protected with a mutex to preserve data integrity.


## __construct()

    new SharedMemoryParcel(
        mixed $value,
        int $size = 16384,
        int $permissions = 0600
    )

Creates a new local object container.

### Parameters
`$value`
:   The value to store in the container.

`$size`
:   The number of bytes to allocate for the object. If not specified defaults to 16384 bytes.

`$permissions`
:   The access permissions to set for the object. If not specified defaults to `0600`.


## isFreed()

    Parcel::isFreed(): bool

Checks if the object has been freed.

Note that this does not check if the object has been destroyed; it only checks if this handle has freed its reference to the object.


## free()

    Parcel::free()

Frees the shared object from memory. The memory containing the shared value will be invalidated. When all process disconnect from the object, the shared memory block will be destroyed by the OS.

Calling `free()` on an object already freed will have no effect. If this method is not called, the parcel will remain in memory until the system is restarted.
