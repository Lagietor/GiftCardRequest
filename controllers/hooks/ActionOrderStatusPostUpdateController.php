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

        // TODO: remove?
        // $collector = new OrderPaidCollector($params['id_order']);
        // $data = serialize($collector->getData());
        // $this->sendData($params['id_order'], $data);

        $webhook = new WebhookHandler($params['id_order'], $params['newOrderStatus']->id);
        $webhook->handle();
    }

    // TODO: remove?
    // public function sendData(int $idOrder, string $data)
    // {
    //     $query = 'INSERT INTO `ps_giftcardrequest_data`(`id_order`, `data`)
    //     VALUES (' . $idOrder . ",  '$data')";

    //     //dump($query);
    //     dump(Db::getInstance()->execute($query));
    //     die;
    // }
}
