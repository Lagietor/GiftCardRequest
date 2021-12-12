<?php

namespace Gcr\DataCollector;

use Gcr\Core\DataCollectorBase;
use Gcr\Core\DefaultDataCollectorTrait;

class OrderPaidCollector extends DataCollectorBase
{
    protected const STATUS_TYPE = 3; // 1 for new, 3 for paid

    use DefaultDataCollectorTrait {
        getData as protected getDataTrait;
    }

    public function getData(): array
    {
        $data = $this->getDataTrait();

        // set values for this collector
        $data['paid'] = $data['sum'];

        return $data;
    }
}
