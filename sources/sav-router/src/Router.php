<?php
namespace SavRouter;

use PathRoute\PathRoute;
use SavUtil\CaseConvert;

class Router
{
    public function __construct($opts = array())
    {
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
        $this->allRoutes = array();
        $this->absoluteRoutes = $this->createMethods(array());
    }
    public function getRoutes()
    {
        return $this->allRoutes;
    }
    public function build($target)
    {
        if (isset($target["modal"])) {
            return $this->createActionRoute($target);
        }
        return $this->createModalRoute($target);
    }
    public function load($data)
    {
        if (isset($data['modals'])) {
            $modals = $data['modals'];
            foreach ($modals as $key => $it) {
                if (!is_numeric($key)) {
                    $it['name'] = $key;
                }
                $this->build($it);
            }
        }
        if (isset($data['actions'])) {
            foreach ($data['actions'] as $it) {
                $this->build($it);
            }
        }
    }
    public function createModalRoute($opts)
    {
        $route = array(
            "name" => CaseConvert::pascalCase($opts['name']),
            "path" => CaseConvert::convert($this->opts['caseType'], $opts['name']),
            "opts" => $opts,
            "keys" => array(),
            "childs" => $this->createMethods(array())
        );
        $path = (isset($opts['path']) && is_string($opts['path'])) ? $opts['path'] : $route['path'];
        $route['path'] = $this->normalPath('/' . $path);
        $route['regexp'] = PathRoute::parse($route['path'], array(
            "sensitive" => $this->opts['sensitive'],
            "end" => false))['regexp'];
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
                $this->build($it);
            }
        }
        return $route;
    }
    public function createActionRoute($opts)
    {
        $modal = &$this->modalMap[$opts['modal']];
        if (isset($opts['method'])) {
            $method = $opts['method'];
        } elseif (isset($modal['view'])) {
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
            "keys" => array()
        );
        $isAbsolute = false;
        $path = (isset($opts['path']) && is_string($opts['path'])) ? $opts['path'] : null;
        if ($path && $path[0] === '/') {
            $isAbsolute = true;
        } else {
            $path = $modal['path'] . '/' . (is_null($path) ? $route['path'] : $path);
        }
        $path = $this->normalPath($path);
        if ($path[strlen($path) -1] == "/") {
            $path = substr($path, 0, strlen($path) -1);
        }
        $route['path'] = $path;
        $parsed = PathRoute::parse($route['path'], array(
            "sensitive" => $this->opts['sensitive'],
            "strict" => false,
            "end" => true
        ));
        $route['regexp'] = $parsed['regexp'];
        $route['keys'] = $parsed['keys'];
        $route['complie'] = PathRoute::complie($parsed['tokens']);
        $route['isAbsolute'] = $isAbsolute;
        if ($isAbsolute) {
            $this->absoluteRoutes[$method][] = &$route;
            $this->absoluteRoutes['ANY'][] = &$route;
        } else {
            $modal['childs'][$method][] = &$route;
            $modal['childs']['ANY'][] = &$route;
        }
        $this->allRoutes[] = &$route;
        return $route;
    }
    public function matchRoute($path, $method)
    {
        $method = strtoupper($method);
        if ($method === 'OPTIONS') {
            $method = 'ANY';
        }
        if (array_search($method, static::$methods) === false) {
            return;
        }
        $path = self::stripPrefix($path, $this->opts['prefix']);
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
    private function matchRouteItem($route, $path, &$ret)
    {
        $params = PathRoute::match($route, $path);
        if (is_array($params)) {
            $ret['params'] = $params;
            $ret['route'] = $route;
            return true;
        }
    }
    private function createMethods($target)
    {
        foreach (Router::$methods as $name) {
            $target[$name] = array();
        }
        return $target;
    }
    private function normalPath($path)
    {
        return preg_replace('/\/+/', '/', $path);
    }
    public static function stripPrefix($src, $prefix)
    {
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
    public static $methods = array('GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'ANY');
}
