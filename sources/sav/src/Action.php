<?php
namespace Sav;

class Action
{
    function __construct($opts, $container)
    {
        $this->opts = $opts;
        $this->container = $container;
    }
    public function fetch($data = array())
    {
        return $this->container->fetch($this, $data);
    }
    public function queue($opts = array())
    {
        return $this->container->queue($this, $data);
    }
}
