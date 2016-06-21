<?php
namespace coco\db;

use PDO;
use CoCo;

/**
 * Table
 * User: ttt
 * Date: 2016/3/16
 * Time: 11:24
 */
class Table
{
    /**
     * @var Db|null
     */
    protected $db;

    /**
     * @var string
     */
    protected $dbName;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string table's primary key
     */
    protected $primaryKey;

    /**
     * @var string fields in select query
     */
    protected $fields;

    /**
     * @var string where conditions
     */
    protected $where;

    /**
     * @var array where bound params
     */
    protected $bindParams = [];

    /**
     * @var string order by
     */
    protected $order;

    /**
     * @var string group by
     */
    protected $group;

    /**
     * @var int limit
     */
    protected $limit;

    /**
     * @var int offset
     */
    protected $offset;

    /**
     * Table constructor.
     * @param null|Db $db
     */
    public function __construct(Db $db = null)
    {
        if (is_null($db)) {
            $this->db = CoCo::$app->db;
        } else {
            $this->db = $db;
        }
    }

    /**
     * get Db instance
     * @return Db|null
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * return last execute sql
     * @return string
     */
    public function getLastSql()
    {
        return $this->db->getLastSql();
    }

    /**
     * insert a row
     * @param array $insertData
     * @return bool false|int lastInsertId
     */
    public function insert(array $insertData)
    {
        return $this->db->insert($this->tableName, $insertData);
    }

    /**
     * update
     * @param array $setData
     * @param string $where
     * @param array $bindParams
     * @return bool false|int affected rows
     */
    public function update(array $setData, $where, array $bindParams = [])
    {
        return $this->db->update($this->tableName, $setData, $where, $bindParams);
    }

    /**
     * delete
     * @param string $where
     * @param array $bindParams
     * @return bool false|int affected rows
     */
    public function delete($where = '', $bindParams = [])
    {
        return $this->db->delete($this->tableName, $where, $bindParams);
    }

    /**
     * select fields
     * @param string $fields
     * @return $this
     */
    public function select($fields = '*')
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * where condition
     * @param string $where
     * @param array $bindParams
     * @return $this
     */
    public function where($where, array $bindParams = [])
    {
        $this->where = $where;
        $this->bindParams = $bindParams;
        return $this;
    }

    /**
     * limit
     * @param int $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * offset
     * @param int $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * group by
     * @param string $group
     * @return $this
     */
    public function group($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * order by
     * @param string $order
     * @return $this
     */
    public function order($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * combine sql
     * @return string
     */
    protected function combineSql()
    {
        $sql = 'SELECT ';

        if (!empty($this->fields)) {
            $sql .= $this->fields;
        }

        $sql .= ' FROM ' . $this->tableName;

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->where;
        }

        if (!empty($this->group)) {
            $sql .= ' GROUP BY ' . $this->group;
        }

        if (!empty($this->order)) {
            $sql .= ' ORDER BY ' . $this->order;
        }

        if (isset($this->limit)) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if (isset($this->offset)) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    /**
     * reset attributes
     */
    protected function resetAttr()
    {
        $this->fields = null;
        $this->where = null;
        $this->bindParams = [];
        $this->group = null;
        $this->order = null;
        $this->limit = null;
        $this->offset = null;
    }

    /**
     * fetch all rows
     * @param int $fetchStyle
     * @return array|bool|mixed
     */
    public function fetchAll($fetchStyle = PDO::FETCH_ASSOC)
    {
        $sql = $this->combineSql();
        $res = $this->db->fetchAll($sql, $this->bindParams, $fetchStyle);
        $this->resetAttr();
        return $res;
    }

    /**
     * fetch first row
     * @param int $fetchStyle
     * @return array|bool
     */
    public function fetch($fetchStyle = PDO::FETCH_ASSOC)
    {
        $sql = $this->combineSql();
        $res = $this->db->fetch($sql, $this->bindParams, $fetchStyle);
        $this->resetAttr();
        return $res;
    }
}