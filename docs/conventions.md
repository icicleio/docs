This documentation uses several conventions to help explain concepts and document APIs.

`Monospace text` is used for class names, function names, and variable names. It is also used for text that should be typed verbatim in the terminal.

Pay special attention to different types of notes as well.

!!! note
    This is a note. Notes contain supplementary information set apart from the text.

!!! tip
    This is a tip. Tips offer helpful advice on how something can be used or on best practices.

!!! warning
    This is a warning. Warnings help you avoid annoying problems or unexpected behavior.

Prototypes for object instance methods are described in this documentation using the following syntax:

```php
ClassOrInterfaceName::methodName(ArgumentType $arg): ReturnType
```

Prototypes for static object methods are described in this documentation using the following syntax:

```php
static ClassOrInterfaceName::methodName(ArgumentType $arg): ReturnType
```

Prototypes for functions in a namespace are described in this documentation using the following syntax:

```php
Namespace\functionName(ArgumentType $arg): ReturnType
```

To document the expected prototype of a callback function used as method arguments or return types, this documentation uses the following syntax for `callable` types:

```php
callable<(ArgumentType $arg): ReturnType>
```
