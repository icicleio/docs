An object for asynchronously spawning and managing an external process.

**Implements**
:   [`Process`](Process.md)


### Process::__construct()

    Process::__construct(string $command, string $cwd = '', array $env = [], array $options = [])

Creates a new process. The process will be run by executing the command specified by `$command`.

The working directory can be set by specifying `$cwd`. If `$cwd` is empty, the process will inherit the working directory of the current process.

Additional environment variables can be passed to the process by passing in an array of values to `$env`, where keys and values correspond to the names of the variables and their values.

Other options can be passed to [`proc_open()`](http://php.net/proc_open) in `$options`. See the documentation for [`proc_open()`](http://php.net/proc_open) for details.

### start()

    Process::start(): void

Starts the process execution.

### join()

    Process::join(): \Generator

Resolves when the process ends.

!!! note
    [**Coroutine**](../../manual/coroutines.md): Calls to this function must be preceded with `yield` within another coroutine or wrapped with `new Coroutine()` to create an awaitable.

### kill()

    Process::kill(): void

Forcefully kills the process.

### signal()

    Process::signal(int $signo): void

Sends the given POSIX process signal to the running process.

#### Parameters
`int $signo`
:   A POSIX signal number. Use constants such as `SIGTERM`, `SIGCHLD`, etc.

### getPid()

    Process::getPid(): int

Returns the PID of the child process. Value is only meaningful if the process has been started and PHP was not compiled with ``--enable-sigchild`.

### getCommand()

    Process::getCommand(): string

Gets the command to execute. Returns the current working directory or null if inherited from the current PHP process.

### getWorkingDirectory()

Gets the current working directory.

### getEnv()

    Process::getEnv(): array

Gets an associative array of environment variables passed to the process.

### getOptions()

    Process::getOptions(): array

Gets the options to pass to [`proc_open()`](http://php.net/proc_open).

### isRunning()

    Process::isRunning(): bool

Determines if the process is still running.

### getStdIn()

    Process::getStdIn(): Icicle\Stream\WritableStream

Gets the process input stream (STDIN).

### getStdOut()

    Process::getStdIn(): Icicle\Stream\ReadableStream

Gets the process output stream (STDERR).

### getStdErr()

    Process::getStdIn(): Icicle\Stream\ReadableStream

Gets the process error stream (STDOUT).
