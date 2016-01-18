An execution context that uses a separately executed PHP process.

**Implements**
:   [`Process`](Process.md)
:   [`Strand`](Strand.md)


## __construct()

    new ChannelledProcess(
        string $path,
        string $cwd = '',
        array $env = []
    )

### Parameters
`$path`
:   Path to PHP script.

`$cwd`
:   Working directory.

`$env`
:   Array of environment variables.
