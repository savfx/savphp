<?php

namespace SavRouter;

class Router
{
  public function __construct ($options = array()) {
    $this->options = array_merge($this->options, $options);
  }
  public function declare ($value) {
    foreach (array('modals', 'actions') as $group) {
      if (isset($value[$group])) {
        foreach ($value[$group] as $key => $val) {
          if (!isset($val['name'])) { // assoc array
            $val['name'] = $key;
          }
          array_push($this->{$group}, $this->buildRoute($group, $val));
        }
      }
    }
  }
  protected function buildRoute ($type, $value) {
    if (!isset($value['path'])) {
      $value['path'] = $value['name'];
    }
    $path = $value['path'];
    if ($type === 'modal') {
      if ($path === '') {
        $path = '/';
      }
      if (!isset($value['match'])) {
        if (false !== strpos($path, ':')) {

        }
      }
    } else { // action

    }
    return $value;
  }
  private $actions = array();
  private $modals = array();
  private $options = array(
    'modalDir' => 'modals' ,
    'baseUrl' => '',
  );
}

$r = new Router();
$r->declare(array(
    "modals" => array(
      array("name" => "Account"),
      array("name" => "Article"),
    ),
    "actions" => array(
      array("modal" => "Account", "name" => "login"),
      array("modal" => "Article", "name" => "list"),
    )
  ));
var_dump($r);
