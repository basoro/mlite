<?php

namespace Systems\Lib;

class QueryWrapper
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

    public function __construct($table = null)
    {
        if ($table) {
            $this->table = $table;
        }
    }

    public static function pdo()
    {
        return static::$db;
    }

    public static function lastSqls()
    {
        return static::$last_sqls;
    }

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
            'error_mode'    => \PDO::ERRMODE_WARNING,
            'json_options'  => JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT,
            ], $options);
        static::$db = new \PDO($dsn, $user, $pass);
        static::$db->setAttribute(\PDO::ATTR_ERRMODE, static::$options['error_mode']);
    }
    public static function close()
    {
        static::$db = null;
    }

    public static function config($name, $value = null)
    {
        if ($value === null) {
            return static::$options[$name];
        } else {
            static::$options[$name] = $value;
        }
    }

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

    public function join($table, $condition)
    {
        array_push($this->joins, "INNER JOIN $table ON $condition");
        return $this;
    }

    public function leftJoin($table, $condition)
    {
        array_push($this->joins, "LEFT JOIN $table ON $condition");
        return $this;
    }

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
            $value = "?";
        }

        if (empty($this->conditions) || strpos(end($this->conditions), '(') !== false) {
            array_push($this->conditions, "$column $operator $value");
        } else {
            array_push($this->conditions, "$ao $column $operator $value");
        }

        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'OR');
    }

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

    public function orIsNull($column)
    {
        return $this->isNull($column, 'OR');
    }

    public function orIsNotNull($column)
    {
        return $this->isNotNull($column, 'OR');
    }

    public function like($column, $value)
    {
        $this->where($column, 'LIKE', $value);
        return $this;
    }

    public function orLike($column, $value)
    {
        $this->where($column, 'LIKE', $value, 'OR');
        return $this;
    }

    public function notLike($column, $value)
    {
        $this->where($column, 'NOT LIKE', $value);
        return $this;
    }

    public function orNotLike($column, $value)
    {
        $this->where($column, 'NOT LIKE', $value, 'OR');
        return $this;
    }

    public function in($column, $values)
    {
        $this->where($column, 'IN', $values);
        return $this;
    }

    public function orIn($column, $values)
    {
        $this->where($column, 'IN', $values, 'OR');
        return $this;
    }

    public function notIn($column, $values)
    {
        $this->where($column, 'NOT IN', $values);
        return $this;
    }

    public function orNotIn($column, $values)
    {
        $this->where($column, 'NOT IN', $values, 'OR');
        return $this;
    }

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

    public function update($column = null, $value = null)
    {
        if ($column) {
            $this->set($column, $value);
        }
        return $this->_build(['only_update' => true]);
    }

    public function asc($column)
    {
        array_push($this->orders, "$column ASC");
        return $this;
    }

    public function desc($column)
    {
        array_push($this->orders, "$column DESC");
        return $this;
    }

    public function rand()
    {
        array_push($this->orders, "RAND()");
        return $this;
    }

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

    public function limit($num)
    {
        $this->limit = " LIMIT $num";
        return $this;
    }

    public function offset($num)
    {
        $this->offset = " OFFSET $num";
        return $this;
    }

    public function toArray()
    {
        $st = $this->_build();
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function toObject()
    {
        $st = $this->_build();
        return $st->fetchAll(\PDO::FETCH_OBJ);
    }

    public function toJson()
    {
        $rows = $this->toArray();
        return json_encode($rows, static::$options['json_options']);
    }

    public function oneArray($column = null, $value = null)
    {
        if ($column !== null) {
            $this->where($column, $value);
        }
        $st = $this->_build();
        //return $st->fetch(\PDO::FETCH_ASSOC);
        return $st->fetch(\PDO::FETCH_ASSOC) ? : [];
    }

    public function oneObject($column = null, $value = null)
    {
        if ($column !== null) {
            $this->where($column, $value);
        }
        $st = $this->_build();
        return $st->fetch(\PDO::FETCH_OBJ);
    }

    public function oneJson($column = null, $value = null)
    {
        if ($column !== null) {
            $this->where($column, $value);
        }
        $row = $this->oneArray();
        return json_encode($row, static::$options['json_options']);
    }

    public function count()
    {
        $st = $this->_build('count');
        return $st->fetchColumn();
    }

    public function lastInsertId()
    {
        return static::$db->lastInsertId();
    }

    public function delete($column = null, $value = null)
    {
        if ($column !== null) {
            $this->where($column, $value);
        }
        $st = $this->_build('delete');
        return $st->rowCount();
    }

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
                // Update created_at column if exists
                if (in_array('created_at', $table_cols) && !array_key_exists('created_at', $this->sets)) {
                    $this->set('created_at', time());
                }

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

    protected function _build($type = 'default')
    {
        return $this->_query($this->toSql($type));
    }

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

    protected function _getColumns()
    {
        $q = $this->pdo()->query("DESCRIBE $this->table;")->fetchAll();
        return array_column($q, 'Field');
    }
}
