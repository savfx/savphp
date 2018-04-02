<?php

namespace Sav;

use SavRouter\Router;
use SavSchema\Schema;
use SavUtil\CaseConvert;
use Sav\Context;

class Sav
{

    function __construct($opts = array())
    {
        $this->opts = array(
            'contractFile' => null, // 合约文件
            'modalPath' => 'modals', // 模块目录
            'schemaPath' => 'schemas', // 模型目录
            'namespace' => '', // 模块命名空间
            'classCase' => '', // 类名规范
            'classSuffix' => '', // 模块名称后缀
            'baseUrl' => '',    // 项目基础URL
            'psr' => false, // 使用 psr标准加载模块
            'disableSchemaCheck' => false, // 是否禁用shcema校验
            'disableInputSchema' => false, // 关闭输入校验 (不推荐)
            'disableOutputSchema' => false, // 关闭输出校验
        );
        foreach ($opts as $key => $value) {
            $this->opts[$key] = $value;
        }
        $this->router = new Router($opts);
        $this->schema = new Schema($opts);
        $this->errorHandler = null;
        $this->authHandler = null;
        $this->funcMap = array();
        $this->methodMap = array();
        if ($this->opts["contractFile"]) {
            $this->load(include_once($this->opts["contractFile"]));
        }
    }

    public function load($json)
    {
        $this->router->load($json);
        $this->schema->load($json);
    }

    public function execute($uri = null, $method = null, $data = null, $cli = false)
    {
        if (is_null($uri)) {
            $uri = $_SERVER['REQUEST_URI'];
            $filePath = "/". basename($_SERVER['SCRIPT_FILENAME']);
            if (($pos = strpos($uri, $filePath)) != false) {
                $uri = substr($uri, 0, $pos + 1) . substr($uri, $pos + strlen($filePath) + 1);
            }
        }
        if (is_null($method)) {
            $method = $_SERVER['REQUEST_METHOD'];
        }
        if (is_null($data)) {
            $data = $_REQUEST;
        }
        try {
            $ctx = $this->prepare($uri, $method, $data);
            $data = $this->invoke($ctx);
            if ($cli) {
                return $data;
            }
            echo $data;
        } catch (\Exception $err) {
            if ($cli) {
                throw $err;
            }
            if ($this->errorHandler) {
                return call_user_func_array($this->errorHandler, array(
                    "ctx" => $ctx,
                    "err" => $err,
                ));
            }
            if (isset($err->status)) {
                http_response_code($err->status);
            }
            echo json_encode(array(
                "error" => array(
                    "msg" => $err->getMessage(),
                )
            ));
        }
    }

    public function invoke($ctx, $encode = true)
    {
        try {
            if (isset($ctx->route)) {
                if ($this->authHandler) {
                    call_user_func_array($this->authHandler, array($ctx->route));
                }
                call_user_func($ctx->invoke);
                $data = $ctx->output;
                if ($encode) {
                    if (is_string($data)) {
                        return $data;
                    } elseif (is_array($data) || is_object($data)) {
                        return json_encode($data);
                    }
                }
                return $data;
            }
        } catch (\Exception $err) {
            if (!isset($err->status)) {
                $errCode = $err->getCode();
                $err->status = $errCode ? $errCode : 500;
            }
            throw $err;
        }
        $exp = new \Exception("Not Found", 404);
        $exp->status = 404;
        throw $exp;
    }

    public function prepare($uri, $method, $req, $ctx = null)
    {
        $ctx = $this->buindCtx($ctx);
        if (($pos = strpos($uri, '?')) > 0) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = preg_replace('/\/+/', '/', $uri);
        $mat = $this->matchUrl($uri, $method);
        if ($mat) {
          // 'inSchemaName', 'outSchemaName', 'inSchema', 'outSchema',
          // 'class', 'instance',
            $this->resolveRoute($mat['route'], $ctx);
            $args = $req;
            foreach ($mat['params'] as $key => $value) {
                $args[$key] = $value;
            }
            foreach (array('path', 'route') as $key) {
                $ctx->{$key} = $mat[$key];
            }
            $ctx->input = $args;
        }
        return $ctx;
    }
    /**
     * 设置错误处理函数
     * @param Function $handler 错误处理函数
     */
    public function setErrorHandler($handler)
    {
        $this->errorHandler = $handler;
    }
    /**
     * 设置认证函数
     * @param Function $handler 认证函数
     */
    public function setAuthHandler($handler)
    {
        $this->authHandler = $handler;
    }
    /**
     * 注入属性
     * @param  String $name 属性名称
     * @param  Function|Mixed $func 初始函数或属性值
     */
    public function prop($name, $func)
    {
        $this->funcMap[$name] = $func;
    }
    /**
     * 注入方法
     * @param  String $name 方法名称
     * @param  Function $func 初始函数
     */
    public function method($name, $func)
    {
        $this->methodMap[$name] = $func;
    }

    public function getInstance($ctx, $name)
    {
        if (isset($this->funcMap[$name])) {
            $val = $this->funcMap[$name];
            if (is_callable($val)) {
                $val = call_user_func_array($val, array($ctx));
            }
            return $ctx->{$name} = $val;
        }
    }

    public function callMethod($ctx, $method, $args)
    {
        if (isset($this->methodMap[$method])) {
            $bindName = '_'. $method;
            if (!isset($ctx->{$bindName})) {
                $ctx->{$bindName} = call_user_func_array($this->methodMap[$method], array($ctx));
            }
            return call_user_func_array($ctx->{$bindName}, $args);
        }
    }

    private function resolveRoute($route, $ret)
    {
      // 添加 class method instance request requestSchema response responseSchema
        $this->getRouteClassMethod($route, $ret);
        $this->getRouteSchema($route, $ret, 'request', 'in');
        $this->getRouteSchema($route, $ret, 'response', 'out');
        $ret->instance = $this->getModalInstance($ret->className);
        return $ret;
    }

    private function getRouteClassMethod($route, $ret)
    {
        $cls = $route['modal']['name'];
        $caseType = $this->opts['classCase'];
        if ($caseType) {
            if (is_callable($caseType)) { //TODO 需要配合MSVC类格式?
                $cls = $caseType($cls);
            } else {
                $cls = CaseConvert::convert($caseType, $cls);
            }
        }
        $namespace = $this->opts['namespace']; // Sav
        if ($namespace) {
            $cls = $namespace . "\\" . $cls;
        }
        $classSuffix = $this->opts['classSuffix']; // Sav
        if ($classSuffix) {
            $cls = $cls . $classSuffix;
        }
        $ret->className = $cls;
        $ret->methodName = $route['opts']['name'];
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
        $ret->{$name.'Name'} = $schemaName;
        $ret->{$name.'Schema'} = $struct;
    }

    private function getModalInstance($className)
    {
        static $instances = array();
        if (!isset($instances[$className])) {
            if (!class_exists($className)) {
                if (!$this->opts['psr']) {
                    $filePath = $this->opts['modalPath'] . $className . '.php';
                    require_once($filePath);
                }
            }
            if (!class_exists($className)) {
                return;
            }
            $instances[$className] = new $className();
        }
        return $instances[$className];
    }

    private function buindCtx($ctx = null)
    {
        if (!isset($ctx)) {
            $ctx = new Context($this);
        }
        $ctx->sav = $this;
        $ctx->schema = $this->schema;
        $ctx->invoke = function () use (&$ctx) {
            $ctx->sav->invokeCtx($ctx);
        };
        $ctx->execute = function ($input) use (&$ctx) {
            return call_user_func_array(array($ctx->instance, $ctx->methodName), array($ctx, $input));
        };
        return $ctx;
    }

    private function invokeCtx($ctx)
    {
        $schemaCheck = !$this->opts['disableSchemaCheck'];
        $input = array();
        if ($ctx->inSchema && $schemaCheck &&
        (!$this->opts['disableInputSchema'])) {
            $input = $ctx->inSchema->extract($ctx->input);
        }
        ob_start();
        $output = null;
        $err = null;
        try {
            $output = call_user_func($ctx->execute, $input);
        } catch (\Exception $exp) {
            $err = $exp;
        }
        $len = ob_get_length();
        if ($len) {
            $buf = ob_get_clean();
            if (!isset($output)) {
                $ctx->buffer = $buf;
                $output = $buf;
            }
        }
        if ($err) {
            throw $err;
        }

        if ($ctx->outSchema && $schemaCheck &&
        (!$this->opts['disableOutputSchema'])) {
            $output = $ctx->outSchema->check($output);
        }
        $ctx->output = $output;
    }
    
    public function matchUrl($url, $method)
    {
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
}
