<?php

/**
 * BonCard GiftCard Webhook Request.
 *
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 *
 * @package   Giftcard
 * @version   1.0.2
 * @copyright Copyright (c) 2021 BonCard Polska Sp. z o.o. (https://www.boncard.pl)
 * @license http://opensource.org/licenses/GPL-3.0 Open Software License (GPL 3.0)
 */

namespace Gcr\DataCollector;

use Gcr\Core\DataCollectorBase;
use Gcr\Core\DefaultDataCollectorTrait;
use GcrRequestData;
use stdClass;

class OrderPaidCollector extends DataCollectorBase
{
    protected const STATUS_TYPE = 3; // 1 for new, 3 for paid

    use DefaultDataCollectorTrait {
        getData as protected getDataTrait;
    }

    public function getName(): string
    {
        return 'order.paid';
    }

    public function getData(): stdClass
    {
        $data = $this->getDataTrait();

        // set values for this collector
        $data->paid = $data->sum;
        $data->ip_address = $this->getIpAddressFromOrderCreate();

        return $data;
    }

    private function getIpAddressFromOrderCreate(): string
    {
        $data = GcrRequestData::getDataForOrderPaid($this->idOrder);

        return isset($data->ip_address) ? $data->ip_address : '';
    }
}
