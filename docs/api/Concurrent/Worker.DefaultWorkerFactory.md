The default worker factory.

**Implements**
:   [`Worker\WorkerFactory`](Worker.WorkerFactory.md)

The type of worker created by this factory depends on the extensions available. If multi-threading is enabled, a [`WorkerThread`](Worker.WorkerThread.md) will be created. If threads are not available, a [`WorkerFork`](Worker.WorkerFork.md) will be created if forking is available, otherwise a [`WorkerProcess`](Worker.WorkerProcess.md) will be created.
