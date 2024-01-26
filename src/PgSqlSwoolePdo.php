<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 23/02/2018
 * Time: 17:50
 */

namespace KevinFarias\PgSqlSwoole;

use Exception;
use PDO;
use Swoole\Coroutine\PostgreSQL;
use Swoole\Coroutine\PostgreSQLStatement;

class PgSqlSwoolePdo extends PDO
{
    protected $connection;

    public function __construct($dsn, $options = [])
    {
        $pg = new PostgreSQL();
        $this->setConnection($pg->connect($dsn));
    }

    public function exec($query)
    {
        return $this->prepare($query)->execute();
    }

    public function prepare(string $statement, $driver_options = null): PostgreSQLStatement|false
    {
        return $this->getConnection()->prepare($statement);
    }

    /**
     * @return mixed
     */
    public function getConnection(): PostgreSQL
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     */
    public function setConnection($connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @param null $name
     * @return string|void
     * @throws Exception
     */
    public function lastInsertId($name = null)
    {
        throw new Exception("Error, you must override this method!");
    }

    public function commit(): void
    {
        $this->getConnection()->query('COMMIT');
    }

    public function rollBack(): void
    {
        $this->getConnection()->query('ROLLBACK');
    }

    public function beginTransaction(): void
    {
        $this->getConnection()->query('BEGIN');
    }
}