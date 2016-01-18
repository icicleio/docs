Interface for contexts that wrap a process.

**Extends**
:   [`Context`](Context.md)


## getPid()

    Process::getPid(): int

Gets the process ID (PID) of the running process.

### Return value
The process ID.


## signal()

    Process::signal(int $signo)

Signals the process with a specified signal number.

### Parameters
`$signo`
:   A POSIX signal number. Use constants such as `SIGTERM`, `SIGCHLD`, etc.

### Throws
`Icicle\Concurrent\Exception\StatusError`
:   Thrown if the process is not running and cannot be signaled.
