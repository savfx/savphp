<?php
use Sav\Sav;

describe("Sav", function() {

  it("Sav.base", function () {
    $sav = new Sav(array(
      "schemaPath" => __DIR__ . '/fixtures/schemas/',
      "modalPath" => __DIR__ . '/fixtures/modals/',
    ));
    $sav->load(array(
      "modals" => array(
        array("name" => "Account")
      ),
      "actions" => array(
        array("name" => "login", "modal" => "Account", 
          "request" => "ReqAccountLogin", "response" => "ResAccountLogin")
      ),
    ));
    $mat = $sav->match("/account/login", "POST");
    // var_dump(array_keys($sav->schema->nameMap);
    var_dump($mat);
    expect($sav)->toBeA('object');
  });

});
