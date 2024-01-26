<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 20/02/2018
 * Time: 15:50
 */

namespace KevinFarias\PgSqlSwoole;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;
use Illuminate\Support\Arr;

class PgSqlSwooleConnector extends Connector implements ConnectorInterface
{

    private function buildPostgresConnectionStr($host, $port, $database, $username, $password): string
    {
        return "host=$host port=$port dbname=$database user=$username password=$password";
    }

    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @return \PDO
     * @internal param array $options
     *
     */
    public function connect(array $config)
    {
        $options = $this->getOptions($config);

        $host = Arr::get($config, 'host');
        $port = Arr::get($config, 'port', 5432);
        $database = Arr::get($config, 'database');
        $username = Arr::get($config, 'username');
        $password = Arr::get($config, 'password');
        $url = Arr::get($config, 'url');
        $connectionStr = $url ?: $this->buildPostgresConnectionStr($host, $port, $database, $username, $password);

        $connection = $this->createConnection($connectionStr, $config, $options);

        return $connection;
    }

    /**
     * Create a new PDO connection instance.
     *
     * @param  string $dsn
     * @param  string $username
     * @param  string $password
     * @param  array $options
     * @return PgSqlSwoolePdo
     */
    protected function createPdoConnection($dsn, $username, $password, $options)
    {
        return new PgSqlSwoolePdo($dsn, $options);
    }
}
