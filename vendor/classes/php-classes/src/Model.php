<?php

namespace Hcode;

class Model {

    private $values = [];

    public function __call ($name, $args)
    {
        $method = substr($name, 0, 3);
        $fieldname = substr($name, 3, strlen($name));

        switch($method) 
        {
            case "get":
                return $this->values[$fieldname];
            break;
            
            case "set":
                return $this->values[$fieldname] = $args[0];

        }

    }

    public function setData($data = array())
    {
        foreach ($data as $key => $value) {
            $this->{"set".$key}($value);
        }
    }

    public function getValues()
    {
        return $this->values;
    }
}