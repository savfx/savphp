<?php

include_once '../vendor/autoload.php';

use Sav\Sav;

$sav = new Sav(array(
  "modalPath" => __DIR__ . "/../spec/fixtures/modals/",
  "schemaPath" => __DIR__ . "/../spec/fixtures/schemas/",
));

$sav->load(array(
  "modals" => array(
    array("name" => "Home"),
    array("name" => "Account"),
  ),
  "actions" => array(
    array("name" => "index", "modal" => "Home", "path" => "/", "method" => "GET"),
    array("name" => "login", "modal" => "Account", "request" => "ReqAccountLogin", "response" => "ResAccountLogin")
  ),
));

$sav->execute();
