<?php
namespace coco\db;

/**
 * Table
 * User: ttt
 * Date: 2016/3/16
 * Time: 11:24
 */
class Table{
    protected $_db;
    protected $_dbName = '';
    protected $_name;
    protected $_primary;

    public function __construct()
    {

    }

    public function getDb(){
        if(!is_null($this->_db) && $this->_db instanceof Db){
            return $this->_db;
        }
        $this->_db = new Db();
    }
}