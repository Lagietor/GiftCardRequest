<?php

/**
 * BonCard GiftCard Webhook Request.
 *
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 *
 * @package   Giftcard
 * @version   1.0.0
 * @copyright Copyright (c) 2021 BonCard Polska Sp. z o.o. (https://www.boncard.pl)
 * @license http://opensource.org/licenses/GPL-3.0 Open Software License (GPL 3.0)
 */

namespace Gcr;

use Curl\Curl;
use DbQuery;
use Gcr\Core\DataCollectorBase;
use Gcr\Core\DataCollectorInterface;
use GcrRequestData;
use GcrWebHook;
use Validate;

class WebhookSender
{
    /** @var GcrWebHook */
    private $webhook;

    /** @var int */
    private $idOrder;

    /** @var DataCollectorInterface */
    private $dataCollector;

    /** @var array */
    private $headers = [];

    /** @var GcrRequestData */
    private $reqData;

    public function __construct(GcrWebHook $webhook, int $idOrder)
    {
        $this->webhook = $webhook;
        $this->idOrder = $idOrder;

        $this->init();
    }

    private function init(): void
    {
        $this->dataCollector = DataCollectorBase::getDataCollector(
            $this->webhook->data_collector,
            $this->idOrder
        );

        if (empty($this->webhook->data_collector)) {
            throw new \Exception('Data collector not found');
        }

        $this->getReqData();
    }

    private function getReqData(): void
    {
        $sql = new DbQuery();
        $sql->select('id_giftcardrequest_data')
            ->from('giftcardrequest_data')
            ->where('id_order = ' . (int)$this->idOrder)
            ->where('id_webhook = ' . $this->webhook->id)
            ->where('data_collector = "' . $this->dataCollector->getName() . '"');

        $id = \Db::getInstance()->getValue($sql);

        if ($id) {
            $this->reqData = new GcrRequestData((int)$id);
        } else {
            $this->reqData = new GcrRequestData();
            $this->reqData->id_order = $this->idOrder;
            $this->reqData->id_webhook = $this->webhook->id;
            $this->reqData->url = $this->webhook->url;
            $this->reqData->data_collector = $this->webhook->data_collector;
            $this->reqData->data = json_encode($this->dataCollector->getData());
            $this->reqData->save();

            if (! $this->reqData->id) {
                throw new \Exception('Could not save GcrRequestData model');
            }
        }
    }

    public function send()
    {
        if (! Validate::isLoadedObject($this->reqData)) {
            throw new \Exception(
                'Could not found GcrRequestData - ID Webhook: ' . $this->webhook->id
                . ', ID order: ' . $this->idOrder
            );
        }

        $this->prepareHeaders();
        $curl = new Curl();

        foreach ($this->headers as $name => $value) {
            $curl->setHeader($name, $value);
        }

        $curl->post($this->webhook->url, $this->reqData->data);

        // save HTTP Response Code
        $this->reqData->http_response_code = (int)($curl->getHttpStatusCode());
        $this->reqData->save();
    }

    private function prepareHeaders(): void
    {
        $this->headers = [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Shop-Version' => _PS_VERSION_,
            'X-Shop-Domain' => defined('_PS_BASE_URL_SSL_') ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_,
            'X-Webhook-Id' => $this->webhook->id,
            'X-Webhook-Name' => $this->webhook->data_collector,
            'X-Webhook-Sha1' => sha1(
                $this->webhook->id . ':' . $this->webhook->secure_key . ':' . $this->reqData->data
            ),
        ];
    }
}
