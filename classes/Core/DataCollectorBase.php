<?php

namespace Gcr\Core;

use Gcr\Core\DataCollectorInterface;
use Gcr\DataCollector\OrderCreateCollector;
use Gcr\DataCollector\OrderPaidCollector;

abstract class DataCollectorBase implements DataCollectorInterface
{
    /** @var int */
    protected $idOrder;

    public function __construct(int $idOrder)
    {
        $this->idOrder = $idOrder;
    }

    /**
     * Get info about DataCollectors for config form
     */
    public static function getAllQuery(): array
    {
        return [
            [
                'id_option' => 'order.create',
                'name' => 'New order',
            ],
            [
                'id_option' => 'order.paid',
                'name' => 'Order paid',
            ],
        ];
    }

    public static function getDataCollector(string $name, int $idOrder): ?DataCollectorInterface
    {
        switch ($name) {
            case 'order.create':
                return new OrderCreateCollector($idOrder);
                break;

            case 'order.paid':
                return new OrderPaidCollector($idOrder);
                break;

            default:
                return null;
                break;
        }
    }
}
