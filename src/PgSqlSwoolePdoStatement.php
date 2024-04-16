<?php

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
    private bool $hasParams = false;
    private array $namedParamsIndex = [];

    public function __construct(PostgreSQL $conn, $query)
    {
        $this->query = $this->convertBindingsFromPdoToPGSql($query);
        $this->query = $this->fixQueriesByOperation($this->query);
        $this->params = [];

        $statement = $conn->prepare($this->query);
        if (!$statement) {
            throw new \Exception("Error preparing statement (Query: ".$this->query."): ".json_encode([$conn->error, $conn->errCode, $conn->notices, $conn->prepare($this->query), $conn, $conn->query($this->query)]));
        }
        $this->statement = $statement;
    }

    public function errorCode(): ?string
    {
        return $this->statement->errCode ?? null;
    }

    public function errorInfo(): array
    {
        return $this->statement->error ?? [];
    }

    public function rowCount(): int
    {
        return $this->statement->affectedRows();
    }

    public function bindParam(string|int $param, mixed &$var, int $type = PDO::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool
    {
        if (! is_string($param) && ! is_int($param)) {
            return false;
        }

        $param = ltrim($param, ':');
        $this->params[$param] = &$var;

        return true;
    }

    public function bindValue(string|int $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        if (! is_string($param) && ! is_int($param)) {
            return false;
        }

        if (is_object($value)) {
            if (! method_exists($value, '__toString')) {
                return false;
            } else {
                $value = (string) $value;
            }
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        $param = ltrim($param, ':');
        $this->params[$param] = $value;

        return true;
    }

    public function execute(?array $params = null): bool
    {
        if (! empty($params)) {
            foreach ($params as $key => $value) {
                $this->bindParam($key, $value);
            }
        }

        $params = $this->params;
        $isNamedParams = ! empty($this->namedParamsIndex);
        if ($isNamedParams) {
            $params = [];
            foreach ($this->namedParamsIndex as $param => $indexes) {
                foreach ($indexes as $index) {
                    $params[$index] = $this->params[$param];
                }
            }
        }
        try {
            $result = $this->statement->execute($params);
        } catch (\Throwable $e) {
            dd($this->params, $e);
        }
        $this->resultSert = ($ok = $result !== false) ? $result : [];

        $this->afterExecute();

        if ($result === false) {
            throw new \PDOException("Query: {$this->query}, error: ".json_encode([$this->statement->notices, $this->errorInfo()]), $this->errorCode());
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


    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        $mode = match (isset(func_get_args()[0]) ? ($mode ?? $this->fetchMode) : $this->fetchMode) {
            PDO::FETCH_ASSOC => SW_PGSQL_ASSOC,
            PDO::FETCH_NUM => SW_PGSQL_NUM,
            PDO::FETCH_BOTH => SW_PGSQL_BOTH,
            PDO::FETCH_OBJ => 'object',
            default => SW_PGSQL_ASSOC,
        };
        if ($mode === 'object') {
            $rows = [];
            while ($row = $this->statement->fetchObject()) {
                $rows[] = $row;
            }

            return $rows;
        }

        return $this->statement->fetchAll($mode);
    }

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
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
            } else if ($char === ':' && !$isStringLiteral) {
                // take complete word
                $param = '';
                for ($j = $i + 1; $j < strlen($sql); $j++) {
                    $nextChar = $sql[$j];
                    if (preg_match('/[a-zA-Z0-9_]/', $nextChar)) {
                        $param .= $nextChar;
                    } else {
                        break;
                    }
                }

                $query .= '$'.$paramIndex;
                $i += strlen($param);

                $this->namedParamsIndex[$param] = [...($this->namedParamsIndex[$param]??[]), $paramIndex];

                $paramIndex++;
            } else { // Normal character...
                $query .= $char;
            }
        }

        $this->hasParams = $paramIndex > 1;

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
