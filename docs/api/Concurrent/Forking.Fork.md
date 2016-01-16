An execution context that uses forked processes. Implements [`Icicle\Concurrent\Context`](#context).

As forked processes are created with the [`pcntl_fork()`](http://php.net/pcntl_fork) function, the [PCNTL extension](http://php.net/manual/en/book.pcntl.php) must be enabled to spawn forks. Not compatible with Windows.

### Fork::spawn()

    static Fork::spawn(
        callable(...$args): mixed $function,
        ...$args
    ): Fork

Spawns a new forked process and immediately starts it. All arguments following the function to invoke in the fork will be copied and passed as parameters to the function to invoke.

#### Parameters
`callable(...$args): mixed $function`
:   The function to invoke inside the forked process.

`mixed ...$args`
:   Arguments to pass to `$function`.

### getPid()

    Fork::getPid(): int

Gets the forked process's process ID.

### getPriority()

    Fork::getPriority(): float

Gets the fork's scheduling priority as a percentage.

The priority is a float between 0 and 1 that indicates the relative priority for the forked process, where 0 is very low priority, 1 is very high priority, and 0.5 is considered a "normal" priority. The value is based on the forked process's "nice" value. The priority affects the operating system's scheduling of processes. How much the priority actually affects the amount of CPU time the process gets is ultimately system-specific.

See also: [getpriority(2)](http://linux.die.net/man/2/getpriority)

### setPriority()

    Fork::setPriority(float $priority)

Sets the fork's scheduling priority as a percentage.

#### Parameters
`int $priority`
:   A value between 0 and 1 indicating the relative priority to set.

!!! note
    On many systems, only the superuser can increase the priority of a process.
