<?php

class GcrWebHookHandler
{
    private $currentState;

    public function __construct(int $orderId, int $orderState)
    {
        $this->orderId = $orderId;
        $this->orderState = $orderState;
    }

    public function checkData()
    {
        $orderId = $this->orderId;

        $this->currentState = $this->orderState;

        if ($this->checkState($orderId)) {
            $webhookId = $this->getWebhookId();
            $this->sendWebhook($webhookId);
        }
    }

    public function checkState(): bool
    {
        $query =
        "SELECT " . _DB_PREFIX_ . "giftcardrequest_webhook_order_state.id_order_state FROM
        " . _DB_PREFIX_ . "giftcardrequest_webhook_order_state, " . _DB_PREFIX_ . "giftcardrequest_webhook WHERE
        " . _DB_PREFIX_ . "giftcardrequest_webhook_order_state.id_giftcardrequest_webhook =
        " . _DB_PREFIX_ . "giftcardrequest_webhook.id_giftcardrequest_webhook"
        ;

        $webhooksStates = Db::getInstance()->executeS($query);

        if (!empty($webhooksStates)) {
            $webhooksStates = array_column($webhooksStates, 'id_order_state');

            if (in_array($this->currentState, $webhooksStates)) {
                return true;
            }

            return false;
        } else {
            return false;
        }
    }

    public function getWebhookId(): int
    {
        $query =
        "SELECT id_giftcardrequest_webhook FROM " . _DB_PREFIX_ . "giftcardrequest_webhook_order_state
        WHERE id_order_state = " . $this->currentState
        ;

        $webhookId = Db::getInstance()->getValue($query);

        return $webhookId;
    }

    public function sendwebhook(int $webhookId)
    {
        // dump("Wyślij webhook o takim Id: " . $webhookId);
        // die;

        // $curl = curl_init();
        // curl_setopt_array($curl, [
        //     CURLOPT_RETURNTRANSFER => 1,
        //     CURLOPT_URL => $this->URL,
        //     CURLOPT_SSL_VERIFYPEER => 0,
        //     CURLOPT_SSL_VERIFYHOST => 0,
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        //     CURLOPT_POST => 1,
        //     CURLOPT_POSTFIELDS => json_encode($requestData)
        // ]);

        // $response = curl_exec($curl);

        // $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // curl_close($curl);

        // return ($http_status == 200) ? $response : false;
    }
}
