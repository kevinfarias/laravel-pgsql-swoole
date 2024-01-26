<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 23/02/2018
 * Time: 17:51
 */

namespace KevinFarias\PgSqlSwoole;

use PDOStatement;
use Swoole\Coroutine\PostgreSQLStatement;
use PDO;

class PgSqlSwoolePdoStatement extends PDOStatement
{
    protected string $query;
    protected array $params = [];
    protected PostgreSQLStatement $statement;
    private $fetchMode;

    public function __construct($conn, $query)
    {
        $this->query = preg_replace('/(?<=\s|^):[^\s:]++/um', '?', $query);

        // $this->params = $this->getParamsFromQuery($query);
        $this->params = [];

        $this->statement = $conn->prepare($this->query);
    }

    /*
    protected function getParamsFromQuery($qry)
    {
        $params = [];
        $qryArray = explode(" ", $qry);
        $i = 0;

        while (isset($qryArray[$i])) {
            if (preg_match("/^:/", $qryArray[$i]))
                $params[$qryArray[$i]] = null;
            $i++;
        }

        return $params;
    }
    */

    public function rowCount()
    {
        return $this->statement->numRows();
    }

    public function bindValue(string|int $param, mixed $val, $ignore = null)
    {
        $this->params[$param] = $val;
    }

    public function execute($ignore = null)
    {
        return $this->statement->execute($this->params);
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
}
