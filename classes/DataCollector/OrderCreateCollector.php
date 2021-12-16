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

namespace Gcr\DataCollector;

use Gcr\Core\DataCollectorBase;
use Gcr\Core\DefaultDataCollectorTrait;
use stdClass;

class OrderCreateCollector extends DataCollectorBase
{
    protected const STATUS_TYPE = 1; // 1 for new, 3 for paid
    protected const PAID = 0.0;

    use DefaultDataCollectorTrait {
        getData as protected getDataTrait;
    }

    public function getName(): string
    {
        return 'order.create';
    }

    public function getData(): stdClass
    {
        $data = $this->getDataTrait();

        // set values for this collector
        $data->paid = self::PAID;
        $data->ip_address = $this->getUserIP();

        return $data;
    }
}
