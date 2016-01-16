The built-in [`Worker\WorkerFactory`](#workerworkerfactory) type.

The type of worker created by this factory depends on the extensions available. If multi-threading is enabled, a `WorkerThread` will be created. If threads are not available, a `WorkerFork` will be created if forking is available, otherwise a `WorkerProcess` will be created.
