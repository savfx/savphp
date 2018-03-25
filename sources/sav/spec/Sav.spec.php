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
    $mat = $sav->execute("/account/login", "POST", array(
      "username" => "jetiny",
      "password" => "jetiny",
    ), true);
    expect($mat)->toBeA('string');
    expect($mat)->toEqual(json_encode(array(
      "id" => 123,
      "welcome" => "welcome jetiny"
    )));
  });

});
