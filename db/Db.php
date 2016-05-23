<?php

namespace coco\db;

use CoCo;
use PDO;

/**
 * Db
 * User: ttt
 * Date: 2016/3/16
 * Time: 11:25
 */
class Db extends Connection
{
    public function __construct(array $config = [], array $slave = [])
    {
        if (empty($config)) {
            $dbConfig = $this->getDbConfig();
            $config = $dbConfig['master'];
            $slave = isset($dbConfig['slave']) ? $dbConfig['slave'] : [];
        }
        parent::__construct($config, $slave);
    }

    /**
     * get db config
     * @return array
     */
    public function getDbConfig()
    {
        return CoCo::$app->config['db'];
    }

    /**
     * fetch all rows
     * @param string $sql
     * @param array $bindParams
     * @param int $fetchStyle
     * @return array|bool|mixed
     */
    public function fetchAll($sql, array $bindParams = [], $fetchStyle = PDO::FETCH_ASSOC)
    {
        $statement = $this->query($sql, $bindParams);
        if ($statement) {
            $args = func_get_args();
            $args = array_slice($args, 2);
            $args[0] = $fetchStyle;
            if ($fetchStyle == PDO::FETCH_FUNC) {
                return call_user_func_array([$statement, 'fetchAll'], $args);
            }
            call_user_func_array([$statement, 'setFetchMode'], $args);
            return $statement->fetchAll();
        }
        return false;
    }

    /**
     * fetch first row
     * @param string $sql
     * @param array $bindParams
     * @param int $fetchStyle
     * @return array|bool false|mixed
     */
    public function fetch($sql, array $bindParams = [], $fetchStyle = PDO::FETCH_ASSOC)
    {
        $statement = $this->query($sql, $bindParams);
        if ($statement) {
            $args = func_get_args();
            $args = array_slice($args, 2);
            $args[0] = $fetchStyle;
            if ($fetchStyle == PDO::FETCH_FUNC) {
                return call_user_func_array([$statement, 'fetchAll'], $args);
            }
            call_user_func_array([$statement, 'setFetchMode'], $args);
            return $statement->fetch();
        }
        return false;
    }

    /**
     * 执行查询统计类型语句, 返回具体单个值, 常用于COUNT、AVG、MAX、MIN
     * @param string $sql
     * @param array $bindParams
     * @return bool false | mixed
     */
    public function scalar($sql, array $bindParams = [])
    {
        $statement = $this->query($sql, $bindParams);
        if ($statement && ($data = $statement->fetch(PDO::FETCH_NUM)) !== false) {
            if (isset($data[0])) {
                return $data[0];
            }
        }
        return false;
    }

    /**
     * insert
     * @param $tableName
     * @param array $insertData
     * @return bool false|int lastInsertId
     */
    public function insert($tableName, array $insertData = [])
    {
        if (empty($insertData)) {
            return false;
        }

        $fields = [];
        $bindFields = [];
        $values = [];
        foreach ($insertData as $key => $value) {
            $fields[] = $key;
            $bindFields[] = '?';
            $values[] = $value;
        }
        $sql = "INSERT INTO " . $tableName . "(" . implode($fields, ",") . ") VALUES(" . implode($bindFields, ",") . ")";

        $affectedRows = $this->execute($sql, $values);
        if ($affectedRows) {
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * delete
     * @param string $tableName
     * @param string $where
     * @param array $bindParams
     * @return bool false|int affected rows
     */
    public function delete($tableName, $where = '', $bindParams = [])
    {
        $sql = 'DELETE FROM ' . $tableName . ' WHERE ' . $where;
        return $this->execute($sql, $bindParams);
    }

    /**
     * update
     * @param string $tableName
     * @param array $setData
     * @param string $where
     * @param array $bindParams
     * @return bool false|int affected rows
     */
    public function update($tableName, $setData, $where = '', $bindParams = [])
    {
        if (empty($setData)) {
            return false;
        }

        $fields = [];
        if (is_assoc($bindParams)) { // bind params using :
            $time = time();
            foreach ($setData as $key => $value) {
                $fields[] = "`$key`=:{$key}_set_" . $time;
                $bindParams[":{$key}_set_{$time}"] = $value;
            }
        } else {  // bind params using ?
            $bindSetParams = [];
            foreach ($setData as $key => $value) {
                $fields[] = "`$key`=?";
                $bindSetParams[] = $value;
            }
            if (!empty($bindParams)) {
                foreach ($bindParams as $v) {
                    $bindSetParams[] = $v;
                }
            }
            $bindParams = $bindSetParams;
        }

        $sql = "UPDATE {$tableName} SET " . implode($fields, ',');
        $sql .= " WHERE " . $where;

        return $this->execute($sql, $bindParams);
    }
}