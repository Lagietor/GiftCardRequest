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

use Gcr\Core\HookControllerInterface;
use Gcr\WebhookHandler;

class ActionOrderStatusPostUpdateController implements HookControllerInterface
{
    /** @var Module */
    private $module;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    public function run($params)
    {
        if (!$params['id_order']) {
            return;
        }

        $webhook = new WebhookHandler($params['id_order'], $params['newOrderStatus']->id);
        $webhook->handle();
    }
}
