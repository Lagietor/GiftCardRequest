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

use GcrWebHook;

class WebhookHandler
{
    /** @var int */
    private $currentState;

    /** @var int */
    private $idOrder;

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
    }
}
