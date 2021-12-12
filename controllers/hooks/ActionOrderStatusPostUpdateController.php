<?php

use Gcr\Core\HookControllerInterface;
use Gcr\WebhookHandler;
use Gcr\DataCollector\OrderPaidCollector;

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

        $collector = new OrderPaidCollector($params['id_order']);
        $data = serialize($collector->getData());
        $this->sendData($params['id_order'], $data);

        $webhook = new WebhookHandler($params['id_order'], $params['newOrderStatus']->id);
        $webhook->handle();
    }

    public function sendData(int $idOrder, string $data)
    {
        $date = date('d.m.Y H:i');

        $query = 'INSERT INTO `ps_giftcardrequest_data`(`id_order`, `data`, `sending_date`) 
        VALUES (' . $idOrder . ",  '$data', '$date')";

        Db::getInstance()->execute($query);
    }
}
