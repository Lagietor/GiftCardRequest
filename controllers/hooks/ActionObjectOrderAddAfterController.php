<?php

use Gcr\DataCollector;

class ActionObjectOrderAddAfterController
{

    public function __construct($module, $WebhookURL)
    {
        $this->module = $module;
        $this->URL = $WebhookURL;
    }

    public function run($params)
    {
        //$colector = new OrderPaidCollector($this->idOrder);
    }
}
