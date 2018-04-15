<?php

namespace Sav;

use SavRouter\Router;
use SavSchema\Schema;
use SavUtil\Request;
use Sav\Action;

class Remote
{
    function __construct($opts = array())
    {
        $this->opts = array(
            'contractFile' => null, // 合约文件
            'schemaPath' => '', // 模型目录
            'baseUrl' => '',    // 项目基础URL
            'disableSchemaCheck' => false, // 是否禁用shcema校验
            'disableInputSchema' => false, // 关闭输入校验
            'disableOutputSchema' => false, // 关闭输出校验 (不推荐)
        );
        foreach ($opts as $key => $value) {
            $this->opts[$key] = $value;
        }
        $this->router = new Router($opts);
        $this->schema = new Schema($opts);
        if ($this->opts["contractFile"]) {
            $this->load(include_once($this->opts["contractFile"]));
        }
        $this->actions = array();
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
        foreach ($this->router->getRoutes() as $route) {
            if ($route['name'] == $actionName) {
                $action = new Action($route, $this)
                $this->getRouteSchema($route, $action, 'request', 'input');
                $this->getRouteSchema($route, $action, 'response', 'output');
                return $this->actions[$actionName] = $action;
            }
        }
    }
    public function queue($action, $data)
    {

    }
    public function fetch($action, $data)
    {

    }
    public function fetchAll($requests)
    {

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
