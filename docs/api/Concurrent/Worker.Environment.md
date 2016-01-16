Implements `\ArrayAccess`, `\Countable`.

A persistent object storage type provided by a worker.

When a worker is created, it initializes a new environment object, which is stored in memory that is local to that worker. When a worker executes a task, this persistent environment object is given to the task to use.

An environment is not destroyed until the worker that owns it is shut down.


### exists()

    Environment::exists(string $key): bool

Checks if a given key exists.

#### Parameters
`string $key`
:   The key to check.

#### Return value
True if the key exists, otherwise false.


### get()

    Environment::get(string $key): mixed|null

#### Parameters
`string $key`
:   The key to get.

#### Return value
The value stored for the given key, or `null` if the key does not exist.


### set()

    Environment::set(string $key, mixed $value, int $ttl = 0)

Sets a key/value pair in the environment.

#### Parameters
`string $key`
:   The key to set.

`mixed $value`
:   The value to set.

`int $ttl`
:   Number of seconds until data is automatically deleted. Use 0 for unlimited TTL.


### delete()

    Environment::delete(string $key)

Deletes a value based on its key.

#### Parameters
`string $key`
:   The key to delete.


### count()

    Environment::count(): int

Gets the number of values in the environment.


### clear()

    Environment::clear()

Removes all values.
