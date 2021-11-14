<?php

class ActionOrderStatusPostUpdateController
{

    public function __construct($module, $WebhookURL)
    {
        $this->module = $module;
        $this->URL = $WebhookURL;
    }

    public function run($params)
    {
        print_r($params);
        die();
    }
}
