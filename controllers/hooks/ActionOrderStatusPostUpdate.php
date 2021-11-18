<?php

class ActionOrderStatusPostUpdateController
{

    private $currentState;

    public function __construct($module, $WebhookURL)
    {
        $this->module = $module;
        $this->URL = $WebhookURL;
    }

    public function run($params)
    {
        if (!$params['id_order']) {
            return true;
        }

        $orderId = $params['id_order'];

        $query = "SELECT current_state FROM " . _DB_PREFIX_ . "orders WHERE id_order = " . $orderId;
        $this->currentState = Db::getInstance()->getValue($query);

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
    }
}
