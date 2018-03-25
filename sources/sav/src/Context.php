<?php

namespace Sav;

class Context {

  function __construct($sav) {
    $this->sav = $sav;
  }

  function __get($name) {
    return $this->sav->getInstance($this, $name);
  }

}
