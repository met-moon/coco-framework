<?php
/**
 * Created by PhpStorm.
 * User: len
 * Date: 2015/11/25
 * Time: 11:15
 */

namespace coco\db;

use CoCo;

class EDB
{
    /**
     * a pdo instance
     * @var
     */
    private static $_db;

    protected $dbConfig = [];           //db config
    protected $selectStr = '*';            //查询字符串
    protected $whereStr = '';            //where字符串
    protected $groupStr = '';            //group by字符串
    protected $orderStr = '';            //order by字符串
    protected $limitStr = '';            //limit字符串
    protected $prepareParams = [];        //预处理绑定参数
    protected $tableName;                //表名
    protected $prepareSql;                //准sql
    protected $lastSql;                    //最后执行的sql
    protected $pk;                        //表主键
    protected $fieldList = [];            //表字段

    public function __construct($dbConfig = null)
    {
        if (is_null($dbConfig)) {
            $this->dbConfig = CoCo::$app->config['db'];

        } else {
            $this->dbConfig = $dbConfig;
        }
        $dsn = $this->dbConfig['dsn'];
        $username = isset($this->dbConfig['username']) ? $this->dbConfig['username'] : '';
        $password = isset($this->dbConfig['password']) ? $this->dbConfig['password'] : '';
        $options = isset($this->dbConfig['options']) ? $this->dbConfig['options'] : [];
        $this->connect($dsn, $username, $password, $options);
    }

    public static function model(){

    }

    /**
     * pdo connect db
     * @param string $dsn pdo dsn
     * @param string $username
     * @param string $password
     * @param array $options
     */
    public function connect($dsn, $username, $password, $options)
    {
        //try {
            self::$_db = new \PDO($dsn, $username, $password, $options);
        //} catch (\PDOException $e) {
        //    echo 'DB Connect Error:' . $e->getMessage();
        //}
    }

    /**
     * insert
     * @param string $tableName
     * @param array $columnsData
     * @return bool|int lastInsertId
     */
    public function insert($tableName, $columnsData)
    {
        $this->_setTableName($tableName);
        $this->_loadField();
        if (empty($columnsData)) {
            return false;
        }
        try {
            //过滤data的值
            $fields = array(); //用于存放取出来的字段
            $bindFields = array(); //用于存放取出来的绑定参数字段
            $values = array(); //用于存放取出来的值
            foreach ($columnsData as $key => $value) {
                if (in_array($key, $this->fieldList)) {
                    $fields[] = $key;
                    $bindFields[] = ':' . $key;           //用:key 时
                    //$bindFields[] = '?';              //用？时
                    $values[] = $value;
                }
            }
            $this->lastSql = "INSERT INTO " . $this->tableName . "(" . implode($fields, ",") . ") VALUES(" . implode($bindFields, ",") . ")";
            $stmt = self::$_db->prepare($this->lastSql);
            foreach ($bindFields as $k => $v) {
                $stmt->bindParam($v, $values[$k]);       //用:key 时
                //$stmt->bindParam($k+1,$values[$k]);    //用？时
            }
            $res = $stmt->execute();
            if ($res) {
                return self::$_db->lastInsertId();
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            echo 'SQL ERROR:' . $e->getMessage();
        }
    }

    /**
     * delete
     * @param string $tableName
     * @param array $conditions
     * @return int affected rows
     */
    public function delete($tableName, $conditions = [])
    {
        $this->_setTableName($tableName);
        $this->where($conditions);
        $this->lastSql = 'DELETE FROM `' . $this->tableName . '` ' . $this->whereStr;
        $stmt = self::$_db->prepare($this->lastSql);
        $result = $stmt->execute($this->prepareParams);
        if ($result) {
            return $stmt->rowCount();
        } else {
            return $result;
        }
    }

    /**
     * update
     * @param string $tableName
     * @param array $columnsData
     * @param array $conditions
     * @return int affected rows
     */
    public function update($tableName, $columnsData, $conditions = [])
    {
        if (empty($columnsData)) {
            return false;
        }
        $this->_setTableName($tableName);
        $this->_loadField();
        $fields = [];
        $this->prepareParams = [];
        foreach ($columnsData as $k => $v) {
            if (in_array($k, $this->fieldList) && $k != $this->pk) {
                $fields[] = "`$k`=:{$k}_0";
                $this->prepareParams[":{$k}_0"] = $v;
            }
        }
        $this->prepareSql = "UPDATE `{$this->tableName}` SET " . implode($fields, ',');

        $whereStr = $this->_parseCondition($conditions);
        $this->prepareSql .= $whereStr;
        $this->lastSql = $this->prepareSql;

        $stmt = self::$_db->prepare($this->lastSql);

        $result = $stmt->execute($this->prepareParams);
        if ($result) {
            return $stmt->rowCount();
        } else {
            return $result;
        }
    }

    /**
     * select fields
     * @param string $selectStr default '*'
     * @return $this
     */
    public function select($selectStr = '*')
    {
        $this->selectStr = $selectStr;
        $this->prepareSql = 'SELECT ' . $this->selectStr;
        return $this;
    }

    /**
     * from table
     * @param string $tableName
     * @return $this
     */
    public function from($tableName)
    {
        $this->_setTableName($tableName);
        $this->prepareSql .= ' FROM `' . $this->tableName . '`';
        return $this;
    }

    /**
     * where
     * @param array $conditions
     * @return $this
     */
    public function where($conditions)
    {
        $this->prepareParams = [];
        $this->whereStr = $this->_parseCondition($conditions);
        return $this;
    }

    /**
     * group
     * @param string $groupStr
     * @return $this
     */
    public function group($groupStr = '')
    {
        if (!empty($groupStr)) {
            $this->groupStr = ' GROUP BY ' . $groupStr;
        }
        return $this;
    }

    /**
     * order
     * @param string $orderStr like: 'id DESC'
     * @return $this
     */
    public function order($orderStr = '')
    {
        if (!empty($orderStr)) {
            $this->orderStr = ' ORDER BY ' . $orderStr;
        }
        return $this;
    }

    /**
     * limit
     * @param int $num1
     * @param int $num2
     * @return $this
     */
    public function limit($num1 = null, $num2 = null)
    {
        if (!is_null($num1) && !is_null($num2)) {
            $this->limitStr = " LIMIT $num1,$num2";
        } else if (!is_null($num1) && is_null($num2)) {
            $this->limitStr = " LIMIT $num1";
        }
        return $this;
    }

    /**
     * get a row
     * @param int $fetchType
     * @return array
     */
    public function fetchOne($fetchType = \PDO::FETCH_ASSOC)
    {
        $this->limitStr = ' LIMIT 1';
        $this->_setPrepareSql();
        $this->lastSql = $this->prepareSql;
        $stmt = self::$_db->prepare($this->lastSql);
        $stmt->execute($this->prepareParams);
        return $stmt->fetch($fetchType);
    }

    /**
     * get all
     * @param int $fetchType
     * @return array
     */
    public function fetchAll($fetchType = \PDO::FETCH_ASSOC)
    {
        $this->_setPrepareSql();
        $this->lastSql = $this->prepareSql;
        $stmt = self::$_db->prepare($this->lastSql);
        $stmt->execute($this->prepareParams);
        return $stmt->fetchAll($fetchType);
    }

    /**
     * get offset 0
     * @return mixed
     */
    public function getOne()
    {
        $this->limitStr = ' LIMIT 1';
        $this->_setPrepareSql();
        $this->lastSql = $this->prepareSql;
        $stmt = self::$_db->prepare($this->lastSql);
        $stmt->execute($this->prepareParams);
        return $stmt->fetchColumn();
    }

    /**
     * assemble prepare sql
     * @return string
     */
    private function _setPrepareSql()
    {
        //where
        if (!empty($this->whereStr)) {
            $this->prepareSql .= $this->whereStr;
        }
        //group by
        if (!empty($this->groupStr)) {
            $this->prepareSql .= $this->groupStr;
        }
        //order by
        if (!empty($this->orderStr)) {
            $this->prepareSql .= $this->orderStr;
        }
        //limit
        if (!empty($this->limitStr)) {
            $this->prepareSql .= $this->limitStr;
        }

        return $this->prepareSql;
    }

    /**
     * @param array $conditions
     * @return string
     */
    private function _parseCondition($conditions)
    {
        $this->_loadField();

        $sql = '';
        if (!empty($conditions)) {
            $sqlItem = [];
            if (is_array($conditions)) {
                foreach ($conditions as $k => $v) {
                    $keyArr = explode(' ', $k);
                    if (count($keyArr) == 1) {
                        if (in_array($keyArr[0], $this->fieldList)) {
                            $i = 0;
                            while (array_key_exists($keyArr[0] . '_' . $i, $this->prepareParams)) {
                                $i++;
                            }
                            $prepareKey = ':' . $keyArr[0] . '_' . $i;
                            $this->prepareParams[$prepareKey] = $v;
                            $sqlItem[] = "`$keyArr[0]`=" . $prepareKey;
                        }
                    } else if (count($keyArr) == 2) {
                        if (in_array($keyArr[0], $this->fieldList)) {
                            $i = 0;
                            while (array_key_exists(':' . $keyArr[0] . '_' . $i, $this->prepareParams)) {
                                $i++;
                            }
                            $prepareKey = ':' . $keyArr[0] . '_' . $i;
                            $this->prepareParams[$prepareKey] = $v;
                            $sqlItem[] = "`$keyArr[0]`" . $keyArr[1] . $prepareKey;
                        }
                    }
                }
                if (!empty($sqlItem)) {
                    $sql = implode(' AND ', $sqlItem);
                    $sql = ' WHERE ' . $sql;
                }
            }
        }
        return $sql;
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function getTableName($tableName)
    {
        $this->_setTableName($tableName);
        return $this->tableName;
    }

    /**
     * get a table's primary key
     * @param string $tableName
     * @return string
     */
    public function getPk($tableName)
    {
        $this->_setTableName($tableName);
        $this->lastSql = "SHOW KEYS FROM `$this->tableName` WHERE `Key_name`='PRIMARY'";
        $stmt = self::$_db->query($this->lastSql);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->pk = $row['Column_name'];
        return $this->pk;
    }

    /**
     * @param string $tableName like: tpl_user | {{user}}
     */
    private function _setTableName($tableName)
    {
        $pattern = '/{{(\w+)}}/';
        $res = preg_match($pattern, $tableName, $arr);
        if ($res) {
            $this->tableName = empty($this->dbConfig['tablePrefix']) ? $arr[1] : $this->dbConfig['tablePrefix'] . $arr[1];
        } else {
            $this->tableName = $tableName;
        }
    }

    /**
     * load table fields
     */
    private function _loadField()
    {
        $this->lastSql = 'DESC `' . $this->tableName . '`';
        $result = self::$_db->query($this->lastSql, \PDO::FETCH_ASSOC);
        foreach ($result as $rows) {
            if ($rows['Key'] == 'PRI') {
                $this->pk = $rows['Field'];
            }
            $this->fieldList[] = $rows['Field'];
        }
    }

    /**
     * get last query sql
     * @return string
     */
    public function getLastSql()
    {
        return $this->lastSql;
    }

    /**
     * get current PDO instance
     * @return object PDO instance
     */
    public function getDb()
    {
        return self::$_db;
    }

    /**
     * close pdo link
     */
    public function close()
    {
        self::$_db = null;
    }

    public function __destruct()
    {
        $this->close();
    }


}