<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 23/02/2018
 * Time: 17:51
 */

namespace KevinFarias\PgSqlSwoole;

use PDOStatement;
use Swoole\Coroutine\PostgreSQL;
use Swoole\Coroutine\PostgreSQLStatement;
use PDO;

class PgSqlSwoolePdoStatement extends PDOStatement
{
    protected string $query;
    protected array $params = [];
    protected PostgreSQLStatement $statement;
    private $fetchMode;
    private string $operation = '';
    private ?int $last_insert_id = null;
    private mixed $resultSert;

    public function __construct(PostgreSQL $conn, $query)
    {
        $this->query = $this->convertBindingsFromPdoToPGSql($query);
        $this->query = $this->fixQueriesByOperation($this->query);
        $this->params = [];

        $statement = $conn->prepare($this->query);
        if (!$statement) {
            throw new \Exception("Error preparing statement (Query: ".$this->query."): ".$conn->error);
        }
        $this->statement = $statement;
    }

    public function errorCode()
    {
        return $this->statement->errno;
    }

    public function errorInfo()
    {
        return $this->statement->error;
    }

    public function rowCount()
    {
        return $this->statement->affectedRows();
    }

    public function bindParam($parameter, &$variable, $type = null, $maxlen = null, $driverdata = null)
    {
        if (! is_string($parameter) && ! is_int($parameter)) {
            return false;
        }

        $parameter = ltrim($parameter, ':');
        $this->params[$parameter] = &$variable;

        return true;
    }

    public function bindValue($parameter, $variable, $type = null)
    {
        if (! is_string($parameter) && ! is_int($parameter)) {
            return false;
        }

        if (is_object($variable)) {
            if (! method_exists($variable, '__toString')) {
                return false;
            } else {
                $variable = (string) $variable;
            }
        }

        $parameter = ltrim($parameter, ':');
        $this->params[$parameter] = $variable;

        return true;
    }

    public function execute($inputParameters = null)
    {
        if (! empty($inputParameters)) {
            foreach ($inputParameters as $key => $value) {
                $this->bindParam($key, $value);
            }
        }

        $result = $this->statement->execute($this->params);
        $this->resultSert = ($ok = $result !== false) ? $result : [];

        $this->afterExecute();

        if ($result === false) {
            throw new \PDOException($this->errorInfo(), $this->errorCode());
        }

        return $ok;
    }

    private function afterExecute()
    {
        $this->cursor = -1;
        $this->params = [];
        if ($this->operation === 'insert') {
            $insertedRow = $this->statement->fetchAll();
            $this->last_insert_id = $insertedRow[0]['id'] ?? null;
        } else {
            $this->last_insert_id = null;
        }
    }


    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args)
    {
        $mode = match ($mode ?? $this->fetchMode) {
            PDO::FETCH_ASSOC => SW_PGSQL_ASSOC,
            PDO::FETCH_NUM => SW_PGSQL_NUM,
            PDO::FETCH_BOTH => SW_PGSQL_BOTH,
            default => SW_PGSQL_ASSOC,
        };
        return $this->statement->fetchAll($mode);
    }

    public function fetch($option = null, $ignore = null, $ignore2 = null)
    {
        return $this->statement->fetchArray();
    }

    public function setFetchMode(int $mode, mixed ...$args): void
    {
        $this->fetchMode = $mode;
    }

    public function convertBindingsFromPdoToPGSql(string $sql): string
    {
        $query = '';
        $paramIndex = 1;
        $isStringLiteral = false;

        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $nextChar = $sql[$i + 1] ?? null;

            if ($char === "'") { // Starting / leaving string literal...
                $query .= $char;
                $isStringLiteral = ! $isStringLiteral;
            } elseif ($char === '?' && ! $isStringLiteral) { // Substitutable binding...
                $query .= '$'.$paramIndex;
                $paramIndex++;
            } else { // Normal character...
                $query .= $char;
            }
        }

        return $query;
    }

    private function fixUpdateColumns(string $query): string
    {
        if (strpos($query, 'where') !== false) {
            $query = preg_replace_callback('/update\s+(.*?)\s+set\s+(.*?)\s+where\s+(.*)/i', function ($matches) {
                $table = $matches[1];
                $columns = str_replace("{$table}.", "", $matches[2]);
                $where = $matches[3];
                return 'update '.$table.' set '.$columns.' where '.$where;
            }, $query);
        }

        if (strpos($query, 'where') === false) {
            $query = preg_replace_callback('/update\s+(.*?)\s+set\s+(.*)/i', function ($matches) {
                $table = $matches[1];
                $columns = str_replace("{$table}.", "", $matches[2]);
                return 'update '.$table.' set '.$columns;
            }, $query);
        }

        return $query;
    }

    private function addReturning(string $query): string
    {
        if (str_ends_with($query, 'RETURNING') === false) {
            $query .= ' RETURNING *';
        }

        return $query;
    }

    private function fixQueriesByOperation(string $query): string
    {
        if (strpos(mb_strtolower($this->query), 'update') === 0) {
            $query = $this->fixUpdateColumns($query);
            $this->operation = 'update';
        }
        if (strpos(mb_strtolower($query), 'insert') === 0) {
            $query = $this->addReturning($query);
            $this->operation = 'insert';
        }
        if (strpos(mb_strtolower($query), 'delete') === 0) {
            $this->operation = 'delete';
        }
        if (strpos(mb_strtolower($query), 'select') === 0) {
            $this->operation = 'select';
        }

        return $query;
    }

    public function getLastInsertId(): ?int
    {
        return $this->last_insert_id;
    }
}
