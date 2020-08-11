<?php

namespace Systems\Lib;

/**
 * QueryBuilder class
 */
class QueryBuilder
{
    protected static $db = null;

    protected static $last_sqls = [];

    protected static $options = [];

    protected $table = null;

    protected $columns = [];

    protected $joins = [];

    protected $conditions = [];

    protected $condition_binds = [];

    protected $sets = [];

    protected $set_binds = [];

    protected $orders = [];

    protected $group_by = [];

    protected $having = [];

    protected $limit = '';

    protected $offset = '';

    /**
    * constructor
    *
    * @param string $table
    */
    public function __construct($table = null)
    {
        if ($table) {
            $this->table = $table;
        }
    }

    /**
    * PDO instance
    *
    * @return PDO
    */
    public static function pdo()
    {
        return static::$db;
    }

    /**
    * last SQL queries
    *
    * @return array SQLs array
    */
    public static function lastSqls()
    {
        return static::$last_sqls;
    }

    /**
    * creates connection with database
    *
    * Qb::connect($dsn); // default user, password and options
    * Qb::connect($dsn, $user); // default password and options
    * Qb::connect($dsn, $user, $pass); // default options
    * Qb::connect($dsn, $user, $pass, $options);
    * Qb::connect($dsn, $options);
    * Qb::connect($dsn, $user, $options);
    *
    * @param string $dsn
    * @param string $user
    * @param string $pass
    * @param array $options
    *   primary_key:  primary column name, default: 'id'
    *   error_mode:   default: \PDO::ERRMODE_EXCEPTION
    *   json_options: default: JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    */
    public static function connect($dsn, $user = '', $pass = '', $options = [])
    {
        if (is_array($user)) {
            $options = $user;
            $user = '';
            $pass = '';
        } elseif (is_array($pass)) {
            $options = $pass;
            $pass = '';
        }
        static::$options = array_merge([
            'primary_key'   => 'id',
            'error_mode'    => \PDO::ERRMODE_EXCEPTION,
            'json_options'  => JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT,
            ], $options);
        static::$db = new \PDO($dsn, $user, $pass);
        static::$db->setAttribute(\PDO::ATTR_ERRMODE, static::$options['error_mode']);
    }

    /**
    * close connection with database
    */
    public static function close()
    {
        static::$db = null;
    }

    /**
    * get or set options
    *
    * @param string $name
    * @param mixed $value
    */
    public static function config($name, $value = null)
    {
        if ($value === null) {
            return static::$options[$name];
        } else {
            static::$options[$name] = $value;
        }
    }

    /**
    * SELECT
    *
    * select('column1')->select('column2') // SELECT column1, column2
    * select(['column1', 'column2', ...]) // SELECT column1, column2, ...
    * select(['alias1' => 'column1', 'column2', ...]) // SELECT column1 AS alias1, column2, ...
    *
    * @param string|array $columns
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function select($columns)
    {
        if (!is_array($columns)) {
            $columns = array($columns);
        }
        foreach ($columns as $alias => $column) {
            if (!is_numeric($alias)) {
                $column .= " AS $alias";
            }
            array_push($this->columns, $column);
        }
        return $this;
    }

    /**
    * INNER JOIN
    *
    * @param string $table
    * @param string $condition
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function join($table, $condition)
    {
        array_push($this->joins, "INNER JOIN $table ON $condition");
        return $this;
    }

    /**
    * LEFT OUTER JOIN
    *
    * @param string $table
    * @param string $condition
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function leftJoin($table, $condition)
    {
        array_push($this->joins, "LEFT JOIN $table ON $condition");
        return $this;
    }

    /**
    * HAVING
    *
    * having(aggregate_function, operator, value) // HAVING aggregate_function (=, <, >, <=, >=, <>) value
    * having(aggregate_function, value) // HAVING aggregate_function = value
    *
    * @param string $aggregate_function
    * @param mixed $value
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function having($aggregate_function, $operator, $value = null, $ao = 'AND')
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        if (is_array($value)) {
            $qs = '(' . implode(',', array_fill(0, count($value), '?')) . ')';
            if (empty($this->having)) {
                array_push($this->having, "$aggregate_function $operator $qs");
            } else {
                array_push($this->having, "$ao $aggregate_function $operator $qs");
            }
            foreach ($value as $v) {
                array_push($this->condition_binds, $v);
            }
        } else {
            if (empty($this->having)) {
                array_push($this->having, "$aggregate_function $operator ?");
            } else {
                array_push($this->having, "$ao $aggregate_function $operator ?");
            }
            array_push($this->condition_binds, $value);
        }
        return $this;
    }

    public function orHaving($aggregate_function, $operator, $value = null)
    {
        return $this->having($aggregate_function, $operator, $value, 'OR');
    }

    /**
    * WHERE
    *
    * where(column, operator, value) // WHERE column (=, <, >, <=, >=, <>) value
    * where(column, value) // WHERE column = value
    * where(value) // WHERE id = value
    * where(function($st) {
    *	$st->where()...
    * })
    *
    * @param mixed $column
    * @param mixed $value
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function where($column, $operator = null, $value = null, $ao = 'AND')
    {
        // Where group
        if (!is_string($column) && is_callable($column)) {
            if (empty($this->conditions) || strpos(end($this->conditions), '(') !== false) {
                array_push($this->conditions, '(');
            } else {
                array_push($this->conditions, $ao.' (');
            }

            call_user_func($column, $this);
            array_push($this->conditions, ')');

            return $this;
        }

        if ($operator === null) {
            $value = $column;
            $column = static::$options['primary_key'];
            $operator = '=';
        } elseif ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        if (is_array($value)) {
            foreach ($value as $v) {
                array_push($this->condition_binds, $v);
            }
            $value = '(' . implode(',', array_fill(0, count($value), '?')) . ')';
        } else {
            array_push($this->condition_binds, $value);
        }

        if (empty($this->conditions) || strpos(end($this->conditions), '(') !== false) {
            array_push($this->conditions, "$column $operator ?");
        } else {
            array_push($this->conditions, "$ao $column $operator ?");
        }

        return $this;
    }

    /**
     * OR WHERE
     *
     * orWhere(column, operator, value) // WHERE column (=, <, >, <=, >=, <>) value
     * orWhere(column, value) // WHERE column = value
     * orWhere(value) // WHERE id = value
     * orWhere(function($st) {
     *	$st->where()...
     * })
     *
     * @param mixed $column
     * @param mixed $value
     *
     * @return \Systems\Lib\QueryBuilder
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * WHERE IS NULL
     *
     * @param string $column
     * @param string $ao
     * @return \Systems\Lib\QueryBuilder
     */
    public function isNull($column, $ao = 'AND')
    {
        if (is_array($column)) {
            foreach ($column as $c) {
                $this->isNull($c, $ao);
            }

            return $this;
        }

        if (empty($this->conditions) || strpos(end($this->conditions), '(') !== false) {
            array_push($this->conditions, "$column IS NULL");
        } else {
            array_push($this->conditions, "$ao $column IS NULL");
        }

        return $this;
    }

    /**
     * WHERE IS NOT NULL
     *
     * @param string $column
     * @param string $ao
     * @return \Systems\Lib\QueryBuilder
     */
    public function isNotNull($column, $ao = 'AND')
    {
        if (is_array($column)) {
            foreach ($column as $c) {
                $this->isNotNull($c, $ao);
            }

            return $this;
        }

        if (empty($this->conditions) || strpos(end($this->conditions), '(') !== false) {
            array_push($this->conditions, "$column IS NOT NULL");
        } else {
            array_push($this->conditions, "$ao $column IS NOT NULL");
        }

        return $this;
    }

    /**
     * OR WHERE IS NULL
     *
     * @param string $column
     * @return \Systems\Lib\QueryBuilder
     */
    public function orIsNull($column)
    {
        return $this->isNull($column, 'OR');
    }

    /**
     * OR WHERE IS NOT NULL
     *
     * @param string $column
     * @return \Systems\Lib\QueryBuilder
     */
    public function orIsNotNull($column)
    {
        return $this->isNotNull($column, 'OR');
    }

    /**
    * WHERE LIKE
    *
    * @param string $column
    * @param mixed $value
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function like($column, $value)
    {
        $this->where($column, 'LIKE', $value);
        return $this;
    }

    /**
    * WHERE OR LIKE
    *
    * @param string $column
    * @param mixed $value
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function orLike($column, $value)
    {
        $this->where($column, 'LIKE', $value, 'OR');
        return $this;
    }

    /**
    * WHERE NOT LIKE
    *
    * @param string $column
    * @param mixed $value
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function notLike($column, $value)
    {
        $this->where($column, 'NOT LIKE', $value);
        return $this;
    }

    /**
    * WHERE OR NOT LIKE
    *
    * @param string $column
    * @param mixed $value
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function orNotLike($column, $value)
    {
        $this->where($column, 'NOT LIKE', $value, 'OR');
        return $this;
    }

    /**
    * WHERE IN
    *
    * @param string $column
    * @param array $values
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function in($column, $values)
    {
        $this->where($column, 'IN', $values);
        return $this;
    }

    /**
    * WHERE OR IN
    *
    * @param string $column
    * @param array $values
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function orIn($column, $values)
    {
        $this->where($column, 'IN', $values, 'OR');
        return $this;
    }

    /**
    * WHERE NOT IN
    *
    * @param string $column
    * @param array $values
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function notIn($column, $values)
    {
        $this->where($column, 'NOT IN', $values);
        return $this;
    }

    /**
    * WHERE OR NOT IN
    *
    * @param string $column
    * @param array $values
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function orNotIn($column, $values)
    {
        $this->where($column, 'NOT IN', $values, 'OR');
        return $this;
    }

    /**
    * get or set column value
    *
    * @param string $column
    * @param mixed $value
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function set($column, $value = null)
    {
        if (is_array($column)) {
            $sets = $column;
        } else {
            $sets = [$column => $value];
        }
        $this->sets += $sets;
        return $this;
    }

    /**
    * UPDATE or INSERT
    *
    * @param string $column
    * @param mixed $value
    *
    * @return integer / boolean
    */
    public function save($column = null, $value = null)
    {
        if ($column) {
            $this->set($column, $value);
        }
        $st = $this->_build();
        if ($lid = static::$db->lastInsertId()) {
            return $lid;
        } else {
            return $st;
        }
    }

    /**
    * UPDATE
    *
    * @param string $column
    * @param mixed $value
    *
    * @return boolean
    */
    public function update($column = null, $value = null)
    {
        if ($column) {
            $this->set($column, $value);
        }
        return $this->_build(['only_update' => true]);
    }

    /**
    * ORDER BY ASC
    *
    * @param string $column
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function asc($column)
    {
        array_push($this->orders, "$column ASC");
        return $this;
    }

    /**
    * ORDER BY DESC
    *
    * @param string $column
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function desc($column)
    {
        array_push($this->orders, "$column DESC");
        return $this;
    }

    /**
    * ORDER BY RAND
    *
    * @param string $column
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function rand()
    {
        array_push($this->orders, "RAND()");
        return $this;
    }

    /**
    * GROUP BY
    *
    * @param mixed $column
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function group($columns)
    {
        if (is_array($columns)) {
            foreach ($columns as $column) {
                array_push($this->group_by, "$column");
            }
        } else {
            array_push($this->group_by, "$columns");
        }
        return $this;
    }

    /**
    * LIMIT
    *
    * @param integer $num
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function limit($num)
    {
        $this->limit = " LIMIT $num";
        return $this;
    }

    /**
    * OFFSET
    *
    * @param integer $num
    *
    * @return \Systems\Lib\QueryBuilder
    */
    public function offset($num)
    {
        $this->offset = " OFFSET $num";
        return $this;
    }

    /**
    * create array with all rows
    *
    * @return array
    */
    public function toArray()
    {
        $st = $this->_build();
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
    * create object with all rows
    *
    * @return \stdObject[]
    */
    public function toObject()
    {
        $st = $this->_build();
        return $st->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
    * create JSON array with all rows
    *
    * @return string
    */
    public function toJson()
    {
        $rows = $this->toArray();
        return json_encode($rows, static::$options['json_options']);
    }

    /**
    * create array with one row
    *
    * @param string $column
    * @param mixed $value
    *
    * @return array
    */
    public function oneArray($column = null, $value = null)
    {
        if ($column !== null) {
            $this->where($column, $value);
        }
        $st = $this->_build();
        return $st->fetch(\PDO::FETCH_ASSOC);
    }

    /**
    * create object with one row
    *
    * @param string $column
    * @param mixed $value
    *
    * @return \stdObject
    */
    public function oneObject($column = null, $value = null)
    {
        if ($column !== null) {
            $this->where($column, $value);
        }
        $st = $this->_build();
        return $st->fetch(\PDO::FETCH_OBJ);
    }

    /**
    * create JSON array with one row
    *
    * @param string $column
    * @param mixed $value
    *
    * @return string
    */
    public function oneJson($column = null, $value = null)
    {
        if ($column !== null) {
            $this->where($column, $value);
        }
        $row = $this->oneArray();
        return json_encode($row, static::$options['json_options']);
    }

    /**
    * returns rows count
    *
    * @return integer
    */
    public function count()
    {
        $st = $this->_build('count');
        return $st->fetchColumn();
    }

    /**
     * Last inserted id
     *
     * @return integer
     */
    public function lastInsertId()
    {
        return static::$db->lastInsertId();
    }

    /**
    * DELETE
    *
    * @param string $column
    * @param mixed $value
    */
    public function delete($column = null, $value = null)
    {
        if ($column !== null) {
            $this->where($column, $value);
        }
        $st = $this->_build('delete');
        return $st->rowCount();
    }

    /**
     * Create SQL query
     *
     * @param $type `default`, `delete`, `count`
     *
     * @return string
     */
    public function toSql($type = 'default')
    {
        $sql = '';
        $sql_where = '';
        $sql_having = '';

        // build conditions
        $conditions = implode(' ', $this->conditions);
        $conditions = str_replace(['( ', ' )'], ['(', ')'], $conditions);
        if ($conditions) {
            $sql_where .= " WHERE $conditions";
        }

        // build having
        $having = implode(' ', $this->having);
        if ($having) {
            $sql_having .= " HAVING $having";
        }

        // if some columns have set value then UPDATE or INSERT
        if ($this->sets) {
            // get table columns
            $table_cols = $this->_getColumns();

            // Update updated_at column if exists
            if (in_array('updated_at', $table_cols) && !array_key_exists('updated_at', $this->sets)) {
                $this->set('updated_at', time());
            }

            // if there are some conditions then UPDATE
            if (!empty($this->conditions)) {
                $insert = false;
                $columns = implode('=?,', array_keys($this->sets)) . '=?';
                $this->set_binds = array_values($this->sets);
                $sql = "UPDATE $this->table SET $columns";
                $sql .= $sql_where;

                return $sql;
            }
            // if there aren't conditions, then INSERT
            else {
                $columns = implode(',', array_keys($this->sets));
                $this->set_binds = array_values($this->sets);
                $qs = implode(',', array_fill(0, count($this->sets), '?'));
                $sql = "INSERT INTO $this->table($columns) VALUES($qs)";
                $this->condition_binds = array();

                return $sql;
            }
        } else {
            if ($type == 'delete') {
                // DELETE
                $sql = "DELETE FROM $this->table";
                $sql .= $sql_where;

                return $sql;
            } else {
                // SELECT
                $columns = implode(',', $this->columns);
                if (!$columns) {
                    $columns = '*';
                }
                if ($type == 'count') {
                    $columns = "COUNT($columns) AS count";
                }
                $sql = "SELECT $columns FROM $this->table";
                $joins = implode(' ', $this->joins);
                if ($joins) {
                    $sql .= " $joins";
                }
                $order = '';
                if (count($this->orders) > 0) {
                    $order = ' ORDER BY ' . implode(',', $this->orders);
                }

                $group_by = '';
                if (count($this->group_by) > 0) {
                    $group_by = ' GROUP BY ' . implode(',', $this->group_by);
                }

                $sql .= $sql_where . $group_by . $order . $sql_having . $this->limit . $this->offset;

                return $sql;
            }
        }

        return null;
    }
    /**
    * build SQL query
    *
    * @param array $type `default`, `delete`, `count`
    *
    * @return PDOStatement
    */
    protected function _build($type = 'default')
    {
        return $this->_query($this->toSql($type));
    }

    /**
    * execute SQL query
    *
    * @param string $sql
    *
    * @return PDOStatement
    */
    protected function _query($sql)
    {
        $binds = array_merge($this->set_binds, $this->condition_binds);
        $st = static::$db->prepare($sql);
        foreach ($binds as $key => $bind) {
            $pdo_param = \PDO::PARAM_STR;
            if (is_int($bind)) {
                $pdo_param = \PDO::PARAM_INT;
            }
            $st->bindValue($key+1, $bind, $pdo_param);
        }
        $st->execute();
        static::$last_sqls[] = $sql;
        return $st;
    }

    /**
     * Get current table columns
     *
     * @return array
     */
    protected function _getColumns()
    {
        $q = $this->pdo()->query("DESCRIBE $this->table;")->fetchAll();
        return array_column($q, 'Field');
    }
}
