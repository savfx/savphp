<?php

class Home {

  public function index ($ctx, $input) {
    return file_get_contents(__DIR__ .'/../views/index.html');
  }

}
