<?php

namespace KevinFarias\PgSqlSwoole;

class SwooleDatabaseManager extends \Illuminate\Database\DatabaseManager
{
    public function connection($name = null)
    {
        $prefix = '';
        if (class_exists('Swoole\Coroutine') && \Swoole\Coroutine::getCid() > 0) {
            $prefix = \Swoole\Coroutine::getCid()."_";
        }

        [$database, $type] = $this->parseConnectionName($name);

        $name = $name ?: $database;

        // If we haven't created this connection, we'll create it based on the config
        // provided in the application. Once we've created the connections we will
        // set the "fetch mode" for PDO which determines the query return types.
        if (! isset($this->connections[$prefix.$name])) {
            $this->connections[$prefix.$name] = $this->configure(
                $this->makeConnection($database), $type
            );

            $this->dispatchConnectionEstablishedEvent($this->connections[$prefix.$name]);
        }

        if (class_exists('Swoole\Coroutine') && \Swoole\Coroutine::getCid() > 0) {
            \Swoole\Coroutine\defer(function () use ($prefix, $name) {
                $this->disconnect($prefix.$name);
            });
        }

        return $this->connections[$prefix.$name];
    }
}
