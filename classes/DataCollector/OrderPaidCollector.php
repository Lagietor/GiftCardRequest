<?php

namespace Gcr\DataCollector;

use Gcr\Core\DataCollectorBase;
use Gcr\Core\DefaultDataCollectorTrait;
use GcrRequestData;

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

    public function getData(): array
    {
        $data = $this->getDataTrait();

        // set values for this collector
        $data['paid'] = $data['sum'];
        $data['ip_address'] = $this->getIpAddressFromOrderCreate();

        return $data;
    }

    private function getIpAddressFromOrderCreate(): string
    {
        $data = GcrRequestData::getDataForOrderPaid($this->idOrder);

        return isset($data['ip_address']) ? $data['ip_address'] : '';
    }
}
