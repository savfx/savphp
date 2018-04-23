<?php
namespace Sav;

class Action
{
    function __construct($route, $container)
    {
        $this->route = $route;
        $this->container = $container;
    }
    public function fetch($data = array())
    {
        return $this->container->fetch($this, $data);
    }
    public function queue($data = array())
    {
        return $this->container->queue($this, $data);
    }
}
