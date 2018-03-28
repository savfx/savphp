<?php

namespace Sav;

class Context {

  function __construct($sav) {
    $this->_sav = $sav;
    $this->_datas = array();
  }

  function __get($name) {
    return $this->_sav->getInstance($this, $name);
  }

  function __call($name, $args) {
    return $this->_sav->callMethod($this, $name, $args);
  }

  function set ($name, $value) {
    $this->_datas[$name] = $value;
  }

  function get ($name) {
    if (array_key_exists($name, $this->_datas)) {
      return $this->_datas[$name];
    }
  }

  function all () {
    return $this->_datas;
  }

}
