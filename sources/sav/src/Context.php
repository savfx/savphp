<?php

namespace Sav;

/**
 * 上下文类
 */
class Context
{

    function __construct($sav)
    {
        $this->_sav = $sav;
        $this->_datas = array();
    }
    /**
     * 设置数据
     * @param string $name  字段名称
     * @param mixed $value 字段值
     */
    function set($name, $value)
    {
        $this->_datas[$name] = $value;
    }
    /**
     * 获取数据
     * @param  string $name 字段名称
     */
    function get($name)
    {
        if (array_key_exists($name, $this->_datas)) {
            return $this->_datas[$name];
        }
    }
    /**
     * 获取所有数据
     * @return array
     */
    function all()
    {
        return $this->_datas;
    }

    function __get($name)
    {
        return $this->_sav->getInstance($this, $name);
    }

    function __call($name, $args)
    {
        return $this->_sav->callMethod($this, $name, $args);
    }
}
