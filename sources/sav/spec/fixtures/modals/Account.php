<?php

class Account {

  public function login ($ctx, $input) {
    return array(
      "id" => 123,
      "welcome" => 'welcome ' . $input['username']
    );
  }

}
