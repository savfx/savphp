<?php

namespace Sav;

use SavRouter\Router;
use SavSchema\Schema;
use SavUtil\CaseConvert;

class Sav {

  function __construct($opts = array()) {
    $this->opts = array(
      'modalPath' => 'modals', // 模块目录
      'schemaPath' => 'schemas', // 模型目录
      'namespace' => '', // 模块命名空间
      'auth' => null, // 是否需要认证
      'classCase' => '', // 类名规范
      'classSuffix' => '', // 模块名称后缀
      'baseUrl' => '',    // 项目基础URL
      'psr' => false, // 使用 psr标准加载模块
    );
    foreach ($opts as $key => $value) {
      $this->opts[$key] = $value;
    }
    $this->router = new Router($opts);
    $this->schema = new Schema($opts);
  }

  public function load ($json) {
    $this->router->load($json);
    $this->schema->load($json);
  }

  public function execute() {
    $uri = $_SERVER['REQUEST_URI'];
    if (($pos = strpos($uri, '?')) > 0) {
      $uri = substr($uri, 0, $pos);
    }
    $uri = preg_replace('/\/+/', '/', $uri);
    $this->invoke($uri, $_SERVER['REQUEST_METHOD']);
  }

  public function invoke($url, $methd, $ctx) {
    $mat = $this->matchUrl($url, $method);
    if ($mat) {
      $this->resolveRoute($mat['route'], $mat);
    }
  }

  public function matchUrl($url, $method) {
    if ($this->opts['baseUrl']) {
      $baseUrl = $this->opts['baseUrl'];
      if (strpos($baseUrl, $url) == 0) {
        $url = substr($url, count($baseUrl));
      } else {
        return ;
      }
    }
    $method = strtoupper($method);
    return $this->router->matchRoute($url, $method);
  }

  public function resolveRoute ($route, &$ret) {
    // 添加 class method instance request requestSchema response responseSchema
    $this->getRouteClassMethod($route, $ret);
    $this->getRouteSchema($route, $ret, 'request');
    $this->getRouteSchema($route, $ret, 'response');
    $ret['instance'] = $this->getModalInstance($ret['class']);
    return $ret;
  }

  public function getRouteClassMethod ($route, &$ret) {
    $cls = $route['modal']['name'];
    $ret['method'] = $route['opts']['name'];
    $caseType = $this->opts['classCase'];
    if ($caseType) {
      $cls = CaseConvert::convert($caseType, $cls);
    }
    $namespace = $this->opts['namespace']; // Sav
    if ($namespace) {
      $cls = $namespace . "\\" . $cls;
    }
    $classSuffix = $this->opts['classSuffix']; // Sav
    if ($classSuffix) {
      $cls = $cls . $classSuffix;
    }
    $ret['class'] = $cls;
  }

  public function getRouteSchema ($route, &$ret, $type) {
    $schemaName = null;
    $struct = null;
    $opts = $route['opts'];
    if (isset($opts[$type]) && !empty($opts[$type])) {
      $schemaName = $opts[$type];
      $struct = $this->schema->{$schemaName};
      if (!$struct) {
        $actionName = $route['name'];
        $filePath = $this->opts['schemaPath'] . $actionName . '.php';
        if (file_exists($filePath)) {
          $this->schema->load(include_once ($filePath));
          $struct = $this->schema->{$schemaName};
        }
      }
    }
    $ret[$type] = $schemaName;
    $ret[$type.'Schema'] = $struct;
  }

  public function getModalInstance ($className) {
    static $instances = array();
    if (!isset($instances[$className])) {
      if (!class_exists($className)) {
        if (!$this->opts['psr']) {
          $filePath = $this->opts['modalPath'] . $className . '.php';
          require_once ($filePath);
        }
      }
      if (!class_exists($className)) {
        return;
      }
      $instances[$className] = new $className();
    }
    return $instances[$className];
  }

}
