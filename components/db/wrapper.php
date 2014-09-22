<?php

namespace eternal\functions\classes;

class DB_Wrapper
{

    private $_dblink = NULL;
    private $_stmt = NULL;

    public function __construct($dsn, $user, $pass, $options)
    {
        $this->_dblink = new \PDO($dsn, $user, $pass, $options);
    }

    private function & _query($query)
    {
        $this->_stmt = $this->_dblink->prepare($query);
        return $this;
    }

    private function _cast($value)
    {
        switch (TRUE) {
            case is_int($value): 
                return \PDO::PARAM_INT;
            case is_bool($value): 
                return \PDO::PARAM_BOOL;
            case is_null($value): 
                return \PDO::PARAM_NULL;
            default: 
                return \PDO::PARAM_STR;
        }
    }

    private function _add_params($data)
    {
        if (is_array($data) and ! empty($data)) {
            foreach ($data as $name => $value) {
                $this->_stmt->bindParam(':' . $name, $value, $this->_cast($value));
            }
        }
    }

    private function _execute($data = array())
    {
        $this->_add_params($data);
        $ret = $this->_stmt->execute();
        return $ret;
    }

    public function exec($query, $data = array())
    {
        $this->_query($query);
        return $this->_execute($data);
    }

    public function all($query, $data = array(), $mode = \PDO::FETCH_ASSOC)
    {
        $this->_query($query);
        $this->_execute($data);
        return $this->_stmt->fetchAll($mode);
    }

    public function one($query, $data = array(), $mode = \PDO::FETCH_ASSOC)
    {
        $this->_query($query);
        $this->_execute($data);
        return $this->_stmt->fetch($mode);
    }

}
