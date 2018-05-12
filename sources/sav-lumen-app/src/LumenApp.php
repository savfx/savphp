<?php

namespace SavLumenApp;

use Sav\Sav;
use Laravel\Lumen\Application;

class LumenApp extends Application
{
    public function __construct($basePath = null, $savOptions = array())
    {
        parent::__construct($basePath);
        $opts = array(
            'contractFile' => $basePath.'/contract/contract.php',// 合约文件
            'schemaPath' =>  $basePath.'/contract/schemas/', // 模型目录
            'modalPath' => $basePath.'/app/Http/Controllers', // 模块目录
            'namespace' => 'App\\Http\\Controllers', // 模块命名空间
            'classSuffix' => 'Controller', // 模块名称后缀
            'psr' => true, // 使用 psr标准加载模块
        );
        foreach ($savOptions as $key => $value) {
            $opts[$key] = $value;
        }
        $sav = new Sav($opts);
        $sav->prop('app', $this);
        $this->sav = $sav;
    }
    public function run ($request = null)
    {
        if (!$request) {
          $this->parseIncomingRequest($request);
          $request = $this->request;
        }
        $method = $request->getMethod();
        $path = '/'.trim($request->getPathInfo(), '/');
        $this->sav->prop('request', $request);
        $ctx = $this->sav->prepare($path, $method, $request->toArray());
        if ($ctx->route) {
            try {
                return $this->sendThroughPipeline($this->middleware, function () use (&$ctx){
                    $data = $ctx->sav->invoke($ctx);
                    echo $data;
                });
            } catch (Exception $e) {
                return $this->prepareResponse($this->sendExceptionToHandler($e));
            } catch (Throwable $e) {
                return $this->prepareResponse($this->sendExceptionToHandler($e));
            }
        }
        return parent::run($request);
    }
}
