<?php

namespace SavLumenApp;

use Laravel\Lumen\Application

class LumenApp extends Application
{
    public function __construct($basePath = null)
    {
        parent::__construct($basePath)
    }
    public function run ($request = null)
    {
        if (!$request) {
          $this->parseIncomingRequest();
          $request = $this->request;
        }
        return parent::run($request);
    }
}
