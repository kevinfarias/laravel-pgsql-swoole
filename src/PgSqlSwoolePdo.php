<?php

namespace KevinFarias\PgSqlSwoole;

use Exception;
use PDO;
use Swoole\Coroutine;
use Swoole\Coroutine\PostgreSQL;
use Swoole\Coroutine\PostgreSQLStatement;

class PgSqlSwoolePdo extends PDO
{
    protected array $connections = [];
    protected array $prepared = [];
    protected array $inTransaction = [];

    public function __construct(private string $dsn, private array $options = [])
    {
        $this->getConnection();
    }

    public function exec(string $statement): int|false
    {
        $exec = $this->prepare($statement)->execute();
        if (!$exec) {
            return false;
        }
        return $this->prepared[$this->getPid()]->rowCount();
    }

    public function prepare(string $statement, $driver_options = null): PgSqlSwoolePdoStatement|false
    {
        $prepared = new PgSqlSwoolePdoStatement($this->getConnection(), $statement);
        if (!$prepared) {
            throw new Exception("Error preparing statement: ".$this->getConnection()->error);
        }
        $this->prepared[$this->getPid()] = $prepared;

        return $prepared;
    }

    private function getPid(): int
    {
        return Coroutine::getCid() ?? -1;
    }

    /**
     * @return mixed
     */
    public function getConnection(): PostgreSQL
    {
        if (isset($this->connections[$this->getPid()]))
            return $this->connections[$this->getPid()];

        $connection = new PostgreSQL();
        $connect = $connection->connect($this->dsn);
        if (!$connect) {
            throw new Exception("Error connecting to database: ".$connection->error);
        }

        $this->connections[$this->getPid()] = $connection;

        Coroutine::defer(function () use ($connection) {
            $this->connection = null;
        });

        return $connection;
    }

    /**
     * @param mixed $connection
     */
    public function setConnection($connection): void
    {
        $this->connections[$this->getPid()] = $connection;
    }

    /**
     * @param null $name
     * @return string|void
     * @throws Exception
     */
    public function lastInsertId(?string $name = null): string|false
    {
        $id = $this->prepared[$this->getPid()]->getLastInsertId();
        if (!$id) {
            return false;
        }
        return (string)$id;
    }

    public function commit(): bool
    {
        try {
            $this->getConnection()->query('COMMIT');
            $this->inTransaction[$this->getPid()] = ($this->inTransaction[$this->getPid()] ?? 1) - 1;
            if ($this->inTransaction[$this->getPid()] <= 0) {
                unset($this->inTransaction[$this->getPid()]);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function rollBack(): bool
    {
        try {
            $this->getConnection()->query('ROLLBACK');
            $this->inTransaction[$this->getPid()] = ($this->inTransaction[$this->getPid()] ?? 1) - 1;
            if ($this->inTransaction[$this->getPid()] <= 0) {
                unset($this->inTransaction[$this->getPid()]);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function beginTransaction(): bool
    {
        try {
            $this->getConnection()->query('BEGIN');
            $this->inTransaction[$this->getPid()] = ($this->inTransaction[$this->getPid()] ?? 0) + 1;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function inTransaction(): bool
    {
        return isset($this->inTransaction[$this->getPid()]);
    }
}
