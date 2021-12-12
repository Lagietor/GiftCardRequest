<?php

namespace Gcr;

use Curl\Curl;
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
        $this->prepareHeaders();
    }

    public function send()
    {
        $curl = new Curl();

        foreach ($this->headers as $name => $value) {
            $curl->setHeader($name, $value);
        }

        $curl->post($this->webhook->url, json_encode($this->data));
    }

    private function prepareHeaders(): void
    {
        $this->headers = [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Shop-Version' => _PS_VERSION_,
            'X-Shop-Domain' => defined('_PS_BASE_URL_SSL_') ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_,
            'X-Webhook-Id' => $this->webhook->id, // TODO: id webhooka z bazy danych?
            'X-Webhook-Name' => $this->webhook->data_collector,
            'X-Webhook-Sha1' => sha1(
                $this->webhook->id . ':' . $this->webhook->secure_key . ':' . json_encode($this->data)
            ),
            'X-Shop-License' => 'foo', // TODO: dodać w konfiguracji? zapytać
        ];
    }
}
