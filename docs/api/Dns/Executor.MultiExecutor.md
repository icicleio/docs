Combines multiple executors to send queries to several name servers so queries can be resolved even if some name servers stop responding. Subsequent queries are initially sent to the last server that successfully responded to a query.

**Implements**
:   [`Icicle\Dns\Executor\Executor`](Executor.Executor.md)

### Example

```php
use Icicle\Coroutine\Coroutine;
use Icicle\Dns\Executor\Executor;
use Icicle\Dns\Executor\MultiExecutor;
use Icicle\Loop;
use LibDNS\Messages\Message;

$executor = new MultiExecutor();

$executor->add(new Executor('8.8.8.8'));
$executor->add(new Executor('8.8.4.4'));

// Executor will send query to 8.8.4.4 if 8.8.8.8 does not respond.
$coroutine = new Coroutine($executor->execute('google.com', 'MX'));

$coroutine->done(
    function (Message $message) {
        foreach ($message->getAnswerRecords() as $resource) {
            echo "TTL: {$resource->getTTL()} Value: {$resource->getData()}\n";
        }
    },
    function (Exception $exception) {
        echo "Query failed: {$exception->getMessage()}\n";
    }
);

Loop\run();
```


## add()

    MultiExecutor::add(Executor $executor)

Adds an executor to the multi-executor.

### Parameters
`Icicle\Dns\Executor\Executor $executor`
:   An executor instance to add to the multi-executor.
