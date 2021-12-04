<?php

namespace Gcr\DataCollector;

use ConnectionCore;
use Gcr\Core\DataCollectorBase;

class OrderPaidCollector extends DataCollectorBase
{
    // public function getTableInfo(): array
    // {
    //     $orderData = $this->getOrderData();

    //     foreach ($orderData as $index => $o) {
    //         $data[$index] = $this->getOrderInfo($o['tableName'], $o['idName'], $o['dataName']);
    //     }

    //     return $data;
    // }

    // public function getOrderInfo(string $tableName, string $idName, string $dataName)
    // {
    //     return \Db::getInstance()->getValue(
    //         "SELECT " . $dataName . " FROM " . $tableName . " WHERE " . $idName . " = " . $this->idOrder
    //     );
    // }

    // public function getOrderData(): array
    // {
    //     return [
    //         'user_id' => [
    //             'tableName' => 'ps_orders',
    //             'idName' => 'id_order',
    //             'dataName' => 'id_customer'
    //             ],

    //         'date_add' => [
    //             'tableName' => 'ps_orders',
    //             'idName' => 'id_order',
    //             'dataName' => 'date_add'
    //             ]
    //         ];
    // }

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
        //dump($order);

        $customer = new \Customer($order->id_customer);
        // dump($customer);

        $orderStatusInfo = $this->getOrderStatusInfo();
        // dump($orderStatusInfo);

        $connections = new \Connection($order->id_customer);
        // dump($connection);

        //$product = new \Product();

        $carrier = new \Carrier();
        // dump($carrier);

        $address = new \Address($order->id_address_delivery, $order->id_lang);

        return [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'date' => $order->date_add,
            'status_date' => $orderStatusInfo['date_add'],
            'confirm_date' => $order->date_add,
            'delivery_date' => '',
            'status_id' => $orderStatusInfo['id_order_state'],
            'sum' => round($order->total_paid, 2),
            'payment_id' => \Module::getModuleIdByName($order->module),
            'user_order' => 1, // only register users
            'shipping_id' => $order->id_carrier,
            'shipping_cost' => round($order->total_shipping_tax_incl, 2),
            'email' => $customer->email,
            'delivery_code' => '',
            'code' => '',
            'confirm' => 0,
            'notes' => $order->getFirstMessage(),
            'notes_priv' => '',
            'notes_pub' => '',
            'currency_id' => $order->id_currency,
            'currency_name' => 'PLN', // only PLN supported
            'currency_rate' => 1,
            'paid' => $order->total_paid, // TODO: verify
            'ip_address' => $connections->ip_address, //TODO: verify on others clients
            'discount_client' => '',
            'discount_group' => '',
            'discount_levels' => '',
            'discount_code' => '',
            'shipping_vat' => $carrier->getIdTaxRulesGroupByIdCarrier($order->id_carrier), // pokazuje id ale lepiej byłoby to pobrać z klasy Product
            'shipping_vat_value' => $order->total_shipping_tax_incl - $order->total_shipping_tax_exclude,
            'shipping_vat_name' => '',
            'code_id' => '',
            'lang_id' => $order->id_lang,
            'origin' => '',
            'promo_code' => '',
        ];
    }
}
