<?php

namespace Gcr;

use Gcr\Core\DataCollectorBase;
use Gcr\Core\DataCollectorInterface;
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
