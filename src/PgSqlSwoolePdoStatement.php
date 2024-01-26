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

class PgSqlSwoolePdoStatement extends PDOStatement
{
    protected string $query;
    protected array $params = [];
    protected PostgreSQLStatement $statement;

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

    public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
    {
        return $this->statement->fetchAll();
    }

    public function fetch($option = null, $ignore = null, $ignore2 = null)
    {
        return $this->statement->fetchArray();
    }
}