<?php

namespace Gcr\DataCollector;

use Gcr\Core\DataCollectorBase;

class OrderPaidCollector extends DataCollectorBase
{
    public function getTableInfo(): array
    {
        $orderData = $this->getOrderData();

        foreach ($orderData as $index => $o) {
            $data[$index] = $this->getOrderInfo($o['tableName'], $o['idName'], $o['dataName']);
        }

        return $data;
    }

    public function getData(): array
    {
        $orderData = $this->getTableInfo();
        // TODO: to ma zwrócić array z wszystkimi wymaganymi polami. ID zamówienia jest w $this->idOrder
        return [
            'order_id' => (int)$this->idOrder,
            'user_id' => (int)$orderData['user_id'],
            'date' => (string)$orderData['date_add']
        ];
    }

    public function getOrderInfo(string $tableName, string $idName, string $dataName)
    {
        return \Db::getInstance()->getValue(
            "SELECT " . $dataName . " FROM " . $tableName . " WHERE " . $idName . " = " . $this->idOrder
        );
    }

    public function getOrderData(): array
    {
        return [
            'user_id' => [
                'tableName' => 'ps_orders',
                'idName' => 'id_order',
                'dataName' => 'id_customer'
                ],

            'date_add' => [
                'tableName' => 'ps_orders',
                'idName' => 'id_order',
                'dataName' => 'date_add'
                ]
            ];
    }
}
