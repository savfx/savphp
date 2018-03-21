<?php
namespace SavRouter;

use SavRouter\PathToRegexp;
use SavUtil\CaseConvert;

class Router {
  public function __construct ($opts = array()) {
    $this->opts = array(
      "prefix" => '', 
      "caseType" => 'camel', 
      "sensitive" => true, 
      "method" => 'POST'
    );
    foreach ($opts as $key => $value) {
      $this->opts[$key] = $value;
    }
    $this->modalMap = array();
    $this->modalRoutes = array();
    $this->absoluteRoutes = createMethods(array());
  }
  public function declare($target) {
    if (isset($target["modal"])) {
      return $this->createActionRoute($target);
    }
    return $this->createModalRoute($target);
  }
  public function load($data) {
    if (isset($data['modals'])) {
      $modals = $data['modals'];
      foreach ($modals as $key => $it) {
        if (!is_numeric($key)) {
          $it['name'] = $key;
        }
        $this->declare($it);
      }
    }
    if (isset($data['actions'])) {
      foreach($data['actions'] as $it) {
        $this->declare($it);
      }
    }
  }
  public function createModalRoute($opts) {
    $route = array(
      "name" => CaseConvert::pascalCase($opts['name']),
      "path" => CaseConvert::convert($this->opts['caseType'], $opts['name']), 
      "opts" => $opts, 
      "keys" => array(), 
      "childs" => createMethods(array())
    );
    $path = (isset($opts['path']) && is_string($opts['path'])) ? $opts['path'] : $route['path'];
    $route['path'] = normalPath('/' . $path);
    $route['regexp'] = PathToRegexp::convert($route['path'], $route['keys'], array(
      "sensitive" => $this->opts['sensitive'], 
      "end" => false)
    );
    $this->modalMap[$opts['name']] = &$route;
    if (isset($opts['id'])) {
      $this->modalMap[$opts['id']] = &$route;
    }
    $this->modalRoutes[] = &$route;
    if (isset($opts['routes'])) {
      foreach ($opts['routes'] as $key => $it) {
        if (!is_numeric($key)) {
          $it['name'] = $key;
        }
        $it['modal'] = $opts['name'];
        $this->declare($it);
      }
    }
    return $route;
  }
  public function createActionRoute($opts) {
    $modal = &$this->modalMap[$opts['modal']];
    if (isset($opts['method'])) {
      $method = $opts['method'];
    } else if (isset($modal['view'])) {
      $method = !$modal['view'] ? 'GET' : $this->opts['method'];
    } else {
      $method = $this->opts['method'];
    }
    $route = array(
      "name" => $modal['name'] . CaseConvert::pascalCase($opts['name']), 
      "path" => CaseConvert::convert($this->opts['caseType'], $opts['name']), 
      "opts" => $opts, 
      "method" => $method, 
      "modal" => &$modal, 
      "keys" => array());
    $isAbsolute = false;
    $path = (isset($opts['path']) && is_string($opts['path'])) ? $opts['path'] : null;
    if ($path && $path[0] === '/') {
      $isAbsolute = true;
    } else {
      $path = $modal['path'] . '/' . ($path ? $path : $route['path']);
    }
    $route['path'] = normalPath($path);
    $route['regexp'] = PathToRegexp::convert($route['path'], $route['keys'], array(
      "sensitive" => $this->opts['sensitive'], "end" => true));
    $route['isAbsolute'] = $isAbsolute;
    if ($isAbsolute) {
      $this->absoluteRoutes[$method][] = &$route;
      $this->absoluteRoutes['ANY'][] = &$route;
    } else {
      $modal['childs'][$method][] = &$route;
      $modal['childs']['ANY'][] = &$route;
    }
    return $route;
  }
  public function matchRoute($path, $method) {
    $method = strtoupper($method);
    if ($method === 'OPTIONS') {
      $method = 'ANY';
    }
    if (array_search($method, static::$methods) === false) {
      return;
    }
    $path = stripPrefix($path, $this->opts['prefix']);
    $ret = array("path" => $path);
    foreach ($this->absoluteRoutes[$method] as $route) {
      if ($this->matchRouteItem($route, $path, $ret)) {
        return $ret;
      }
    }
    foreach ($this->modalRoutes as $route) {
      if ($this->matchRouteItem($route, $path, $r)) {
        foreach ($route['childs'][$method] as $subRoute) {
          if ($this->matchRouteItem($subRoute, $path, $ret)) {
            return $ret;
          }
        }
      }
    }
  }
  private function matchRouteItem($route, $path, &$ret) {
    $mat = PathToRegexp::match($route['regexp'], $path);
    if ($mat) {
      if ($ret) {
        $ret['route'] = $route;
        $keys = $route["keys"];
        $params = $ret["params"] = array();
        $len = count($mat); 
        for ($i = 1; $i < $len; ++$i) {
          $key = $keys[$i - 1];
          if ($key) {
            $val = $mat[$i]; // @TODO 路径是否已经是解码后的数据
            // $val = (gettype($mat[$i]) === 'string') ? urldecode($mat[$i]) : $mat[$i];
            $params[$key['name']] = $val;
          }
        }
      }
      return true;
    }
  }
  public static $methods = array('GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'ANY');
}

function createMethods($target) {
  foreach(Router::$methods as $name) {
    $target[$name] = array();
  }
  return $target;
}

function normalPath($path) {
  return preg_replace('/\/+/', '/', $path);
}

function stripPrefix($src, $prefix) {
  if ($prefix) {
    $pos = strpos($src, $prefix);
    if ($pos === 0 || $pos === 1 && $src[0] === '/') {
      $src = substr($src, $pos + count($prefix), count($src));
      if ($src[0] !== '/') {
        $src = '/' + $src;
      }
      return $src;
    }
  }
  return $src;
}
