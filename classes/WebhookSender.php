<?php

namespace Gcr;

use Gcr\Core\DataCollectorBase;
use Gcr\Core\DataCollectorInterface;
use GcrRequestData;
use GcrWebHook;

class WebhookSender
{
    /** @var GcrWebHook */
    private $webhook;

    /** @var int */
    private $idOrder;

    /** @var DataCollectorInterface */
    private $dataCollector;

    // TODO: zaimplementować
    private $headers = [];

    /** @var array */
    private $data = [];

    public function __construct(GcrWebHook $webhook, int $idOrder)
    {
        $this->webhook = $webhook;
        $this->idOrder = $idOrder;

        $this->init();
    }

    private function init()
    {
        $this->dataCollector = DataCollectorBase::getDataCollector(
            $this->webhook->data_collector,
            $this->idOrder
        );

        // TODO: zaimplementować wyjątki. Jeśli $this->dataCollector będzie pusty to wyrzucić wyjątek;

        $this->data = $this->dataCollector->getData();

        // TODO: zapisać obiekt i go potem wysłać
        $reqData = new GcrRequestData();
        $reqData->id_order = $this->idOrder;
        $reqData->data_collector = $this->dataCollector->getName();
        $reqData->data = 'baz';

        if (! $reqData->save()) {
            // TODO: wyjątek
        }

        // TODO: zaimplementować
        $this->send($reqData->id);

        // TODO: przenieść do send()
        $this->headers = $this->getHeaders();
    }

    public function send()
    {
        dump('Próba wysłania webhooka: ' . $this->webhook->id);
        // dump('headers:');
        // dump($this->headers);
        dump('data:');
        dump($this->data);
    }

    // TODO: oprzeć to o array?
    private function getHeaders()
    {
        $this->headers = [];
    }
}
