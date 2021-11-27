<?php

use Gcr\Core\HookControllerInterface;
use Gcr\WebhookHandler;

class ActionOrderStatusPostUpdateController implements HookControllerInterface
{
    /** @var Module */
    private $module;

    public function __construct(\Module $module) // TODO: usunąć jeśli $module nie będzie potrzebne
    {
        $this->module = $module;
    }

    public function run($params)
    {
        if (!$params['id_order']) {
            return;
        }

        $webhook = new WebhookHandler($params['id_order'], $params['newOrderStatus']->id);
        $webhook->handle();
    }
}
