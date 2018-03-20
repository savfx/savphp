<?php

namespace Sav;

use SavRouter\Router;
use SavSchema\Schema;

class Sav {

  function __construct($opts = array()) {
    $this->opts =  array(
      'namespace' => '',
      'auth' => null,
      'classSuffix' => 'Controller',
      'baseUrl' => '',    // 基础URL
    );
    $this->router = new Router($opts);
    $this->schema = new Schema($opts);
  }

  function load ($json) {
    $this->router->load($json);
  }

  function match ($url, $method) {
    if ($this->opts['baseUrl']) {
      $baseUrl = $this->opts['baseUrl'];
      if (strpos($baseUrl, $url) == 0) {
        $url = substr($url, count($baseUrl));
      } else {
        return ;
      }
    }
    $method = strtoupper($method);
    $route = $this->router->matchUrl($url, $method);
  }

}
