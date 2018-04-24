<?php

namespace Sav;

use SavRouter\Router;
use SavSchema\Schema;
use SavUtil\Request;
use Sav\Action;

class Remote
{
    function __construct($opts = array(), $container = null)
    {
        $this->opts = array(
            'contractFile' => null, // 合约文件
            'schemaPath' => '', // 模型目录
            'baseUrl' => '',    // 项目基础URL
            'disableInputCheck' => true, // 关闭输入校验
            'disableOutputCheck' => false, // 关闭输出校验 (不推荐)
            'Request' => '\\SavUtil\\Request', // 请求类
        );
        foreach ($opts as $key => $value) {
            $this->opts[$key] = $value;
        }
        $this->container = $container;
        $this->router = new Router($opts);
        $this->schema = new Schema($opts);
        if ($this->opts["contractFile"]) {
            $this->load(include_once($this->opts["contractFile"]));
        }
        $this->actions = array();
        $this->queues = array();
    }
    /**
     * 加载配置
     * @param  array $json Contract配置
     */
    public function load($json)
    {
        $this->router->load($json);
        $this->schema->load($json);
    }
    public function action($actionName)
    {
        if (isset($this->actions[$actionName])) {
            return $this->actions[$actionName];
        }
        // @TODO 不使用大的contract
        foreach ($this->router->getRoutes() as $route) {
            if ($route['name'] == $actionName) {
                $action = new Action($route, $this);
                $this->getRouteSchema($route, $action, 'request', 'input');
                $this->getRouteSchema($route, $action, 'response', 'output');
                return $this->actions[$actionName] = $action;
            }
        }
    }
    public function queue($action, $data = array())
    {
        if (is_string($action)) {
            $action = $this->action($action);
        }
        $name = $action->route['name'];
        if (array_key_exists($name, $this->queues)) {
            $this->queues[] = array($action, $data);
        } else {
            $this->queues[$name] = array($action, $data);
        }
        return $this;
    }
    public function fetch($action, $data = array())
    {
        if (is_string($action)) {
            $action = $this->action($action);
        }
        if ((!$this->opts['disableInputCheck']) && ($action->inputSchema)) {
            $data = $action->inputSchema->extract($data);
        }
        $path = $action->route['complie']($data);
        $url = $this->opts['baseUrl'] . $path;
        $args = array('url' => $url, 'data' => $data);
        if ($this->container) {
            $args = $this->container->handleRemoteRequest($args, $action, $this);
        }
        $res = call_user_func_array($this->opts['Request'].'::fetch', $args);
        if ((!$this->opts['disableOutputCheck']) && ($action->outputSchema)) {
            $res->response = $action->outputSchema->check($res->response);
        }
        if ($this->container) {
            return $this->container->handleRemoteResponse($res, $action, $this);
        }
        return $res;
    }
    public function fetchAll($requests = null)
    {
        if (is_array($requests)) {
            foreach ($requests as $key => $value) {
                $this->action($key)->queue($value);
            }
        }
        $queues = $this->queues;
        $this->queues = [];
        $argv = array();
        foreach ($queues as $index => $arr) {
            list($action, $data) = $arr;
            if ((!$this->opts['disableInputCheck']) && ($action->inputSchema)) {
                $data = $action->inputSchema->extract($data);
            }
            $path = $action->route['complie']($data);
            $url = $this->opts['baseUrl'] . $path;
            $args = array('url' => $url, 'data' => $data);
            if ($this->container) {
                $args = $this->container->handleRemoteRequest($args, $action, $this);
            }
            $argv[$index] = $args;
        }
        $res = call_user_func_array($this->opts['Request'].'::fetchAll', array($argv));
        foreach ($res as $index => &$item) {
            $action = $queues[$index][0];
            if ((!$this->opts['disableOutputCheck']) && ($action->outputSchema)) {
                $item->response = $action->outputSchema->check($item->response);
            }
            if ($this->container) {
                $item = $this->container->handleRemoteResponse($item, $action, $this);
            }
        }
        return $res;
    }
    private function getRouteSchema($route, &$ret, $type, $name)
    {
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
                    $this->schema->load(include_once($filePath));
                    $struct = $this->schema->{$schemaName};
                }
            }
        }
        $ret->{$name.'SchemaName'} = $schemaName;
        $ret->{$name.'Schema'} = $struct;
    }
}
