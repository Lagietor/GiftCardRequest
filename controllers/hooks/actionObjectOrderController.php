<?php

class ActionObjectOrder
{

    public function __construct($module)
    {
        $this->module = $module;
    }
    public function run()
    {
        Tools::d("test");
    }
}
