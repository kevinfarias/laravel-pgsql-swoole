<?php

namespace KevinFarias\PgSqlSwoole;

class SwooleDatabaseManager extends \Illuminate\Database\DatabaseManager
{
    public function connection($name = null)
    {
        echo "starting connection\n";
        [$database, $type] = $this->parseConnectionName($name);

        $name = $name ?: $database;

        // If we haven't created this connection, we'll create it based on the config
        // provided in the application. Once we've created the connections we will
        // set the "fetch mode" for PDO which determines the query return types.
        if (! isset($this->connections[$name])) {
            $this->connections[$name] = $this->configure(
                $this->makeConnection($database), $type
            );

            $this->dispatchConnectionEstablishedEvent($this->connections[$name]);
        }

        return $this->connections[$name];
    }
}
