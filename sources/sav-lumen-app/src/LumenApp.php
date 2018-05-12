<?php

namespace SavLumenApp;

use Sav\Sav;
use Laravel\Lumen\Application;
use Laravel\Lumen\Http\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
        $sav->setOutputHandler(function($output) {
            if ($output instanceof JsonResponse) {
                return $output->getData(true);
            }
            return $output;
        });
        $this->sav = $sav;
    }
    public function run ($request = null)
    {
        if (!$request) {
          $this->parseIncomingRequest($request);
          $request = $this->request;
        }
        $ctx = $this->createCtx($request);
        if ($ctx->route) {
            $response = $this->invokeCtx($ctx);
            if ($response instanceof SymfonyResponse) {
                $response->send();
            } else {
                echo (string) $response;
            }
            if (count($this->middleware) > 0) {
                $this->callTerminableMiddleware($response);
            }
            return;
        }
        parent::run($request);
    }
    public function handle(SymfonyRequest $request)
    {
        $ctx = $this->createCtx($request);
        if ($ctx->route) {
            $response = $this->invokeCtx($ctx);
            if (count($this->middleware) > 0) {
                $this->callTerminableMiddleware($response);
            }
            return $response;
        }
        return parent::handle($request);
    }
    protected function createCtx($request) {
        $method = $request->getMethod();
        $path = $request->getPathInfo();
        $this->sav->prop('request', $request);
        $ctx = $this->sav->prepare($path, $method, $request->toArray());
        return $ctx;
    }
    protected function invokeCtx($ctx) {
        try {
            return $this->sendThroughPipeline($this->middleware, function () use (&$ctx){
                $data = $ctx->sav->invoke($ctx, false);
                return response($data);
            });
        } catch (Exception $e) {
            return $this->prepareResponse($this->sendExceptionToHandler($e));
        } catch (Throwable $e) {
            return $this->prepareResponse($this->sendExceptionToHandler($e));
        }
    }
}
