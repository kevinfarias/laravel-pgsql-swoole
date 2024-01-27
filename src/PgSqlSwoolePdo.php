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
    protected ?PgSqlSwoolePdoStatement $prepared;

    public function __construct($dsn, $options = [])
    {
        $pg = new PostgreSQL();

        $connect = $pg->connect($dsn);
        if (!$connect) {
            throw new Exception("Error connecting to PostgreSQL server.");
        }

        $this->setConnection($pg);
    }

    public function exec($query)
    {
        return $this->prepare($query)->execute();
    }

    public function prepare(string $statement, $driver_options = null): PgSqlSwoolePdoStatement|false
    {
        $prepared = new PgSqlSwoolePdoStatement($this->getConnection(), $statement);
        if (!$prepared) {
            throw new Exception("Error preparing statement.");
        }
        $this->prepared = $prepared;

        return $prepared;
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
    public function lastInsertId($name = null): ?int
    {
        return $this->prepared->getLastInsertId();
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
