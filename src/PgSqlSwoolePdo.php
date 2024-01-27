<?php

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

    public function exec(string $statement): int|false
    {
        $exec = $this->prepare($statement)->execute();
        if (!$exec) {
            return false;
        }
        return $this->prepared->rowCount();
    }

    public function prepare(string $statement, $driver_options = null): PgSqlSwoolePdoStatement|false
    {
        $prepared = new PgSqlSwoolePdoStatement($this->getConnection(), $statement);
        if (!$prepared) {
            throw new Exception("Error preparing statement: ".$this->getConnection()->error);
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
    public function lastInsertId(?string $name = null): string|false
    {
        $id = $this->prepared->getLastInsertId();
        if (!$id) {
            return false;
        }
        return (string)$id;
    }

    public function commit(): bool
    {
        try {
            $this->getConnection()->query('COMMIT');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function rollBack(): bool
    {
        try {
            $this->getConnection()->query('ROLLBACK');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function beginTransaction(): bool
    {
        try {
            $this->getConnection()->query('BEGIN');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
