<?php

namespace Gcr;

use GcrWebHook;

class WebhookHandler
{
    /** @var int */
    private $currentState;

    private $idOrder; // zmienna do możliwego wykorzystania w przyszłości

    public function __construct(int $idOrder, int $currentState)
    {
        $this->idOrder = $idOrder;
        $this->currentState = $currentState;
    }

    public function handle()
    {
        $allWebhooks = \GcrWebHook::getByOrderState($this->currentState);

        /** @var \GcrWebHook $webhook */
        foreach ($allWebhooks as $webhook) {
            $webhookSender = new WebhookSender(
                new GcrWebHook($webhook->id),
                $this->idOrder
            );
            $webhookSender->send();
        }
        die; // TODO: usunąć po ogarnięciu danych i headerów
    }

    // TODO: move
    public function sendwebhook(int $webhookId)
    {
        dump("Wyślij webhook o takim Id: " . $webhookId);
        die;

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
