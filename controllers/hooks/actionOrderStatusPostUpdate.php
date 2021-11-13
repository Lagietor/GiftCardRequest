<?php

class ActionOrderStatusPostUpdateController
{

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function run($params)
    {
        print_r($params);
        die();
    }
}
