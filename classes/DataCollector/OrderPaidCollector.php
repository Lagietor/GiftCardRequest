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

    private function getOrderStatusInfo(): array
    {
        $sql = new \DbQuery();
        $sql->select('id_order_state, date_add')
            ->from('order_history')
            ->orderBy('id_order_history DESC');

        return \Db::getInstance()->getRow($sql);
    }

    public function getData(): array
    {
        // $orderData = $this->getTableInfo();

        // TODO: walidować czy poprawnie pobrano, jak nie to wyjątek (zaimplementować wyjątki :P)
        $order = new \Order($this->idOrder);
        // dump($order);

        $customer = new \Customer($order->id_customer);
        // dump($customer);

        $orderStatusInfo = $this->getOrderStatusInfo();
        // dump($orderStatusInfo);

        return [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'date' => $order->date_add,
            'status_date' => $orderStatusInfo['date_add'],
            'confirm_date' => '',
            'delivery_date' => '',
            'status_id' => $orderStatusInfo['id_order_state'],
            'sum' => round($order->total_paid, 2),
            'payment_id' => \Module::getModuleIdByName($order->module),
            'user_order' => $customer->is_guest === "1" ? 0 : 1,
        ];
    }
}
