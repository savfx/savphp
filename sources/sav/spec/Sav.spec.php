<?php
use Sav\Sav;

describe("Sav", function() {

  it("Sav.base", function () {
    $sav = new Sav();
    expect($sav)->toBeA('object');
  });

});
