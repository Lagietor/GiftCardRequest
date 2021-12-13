<?php

namespace Gcr\DataCollector;

use Gcr\Core\DataCollectorBase;
use Gcr\Core\DefaultDataCollectorTrait;

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

    public function getData(): array
    {
        $data = $this->getDataTrait();

        // set values for this collector
        $data['paid'] = self::PAID;
        $data['ip_address'] = $this->getUserIP();

        return $data;
    }
}
