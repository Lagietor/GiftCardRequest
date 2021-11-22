<?php

class ActionOrderStatusPostUpdateController implements HookControllerInterface
{

    public function run($params)
    {
        if (!$params['id_order']) {
            return;
        }

        $webhook = new GcrWebHookHandler($params['id_order'], $params['newOrderStatus']->id);
        $webhook->checkData();
    }
}
