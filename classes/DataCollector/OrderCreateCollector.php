<?php

namespace Gcr\DataCollector;

use Gcr\Core\DataCollectorBase;

class OrderCreateCollector extends DataCollectorBase
{
    public function getData(): array
    {
        // TODO: to ma zwrócić array z wszystkimi wymaganymi polami. ID zamówienia jest w $this->idOrder
        // return [
        //     'order_id' => (int)$this->idOrder,
        //     'user_id' => // pobrać ID usera
        //     'date' => // pobrać datę utworzenia zamówienia
        // ];

        return [];
    }
}
