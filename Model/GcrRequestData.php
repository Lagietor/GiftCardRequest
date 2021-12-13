<?php

class GcrRequestData extends \ObjectModel
{
    /** @var int */
    public $id_giftcardrequest_data;

    /** @var int */
    public $id_order;

    /** @var int */
    public $id_webhook;

    /** @var string */
    public $data_collector;

    /** @var string */
    public $data;

    /** @var string */
    public $checksum;

    /** @var string */
    public $date_add;

    public static $definition = [
        'table' => 'giftcardrequest_data',
        'primary' => 'id_giftcardrequest_data',
        'fields' => [
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'id_webhook' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'data_collector' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'data' => [
                'type' => self::TYPE_STRING,
                // TODO: zweryfikować czy trzeba jakoś walidować
                // 'validate' => 'isGenericName',
                'required' => true,
            ],
            'checksum' => [
                'type' => self::TYPE_STRING,
                'size' => [
                    'min' => 40,
                    'max' => 40,
                ],
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    public function save($null_values = false, $auto_date = true)
    {
        $webhook = new GcrWebHook($this->id_webhook);
        // TODO: wyjątek jeśli nie udało się pobrać

        $this->checksum = sha1(
            $webhook->id . ':' . $webhook->secure_key . ':' . $this->data
        );

        parent::save($null_values, $auto_date);
    }

    public static function getDataForOrderPaid(int $idOrder): array
    {
        $sql = new DbQuery();
        $sql->select('data')
            ->from(self::$definition['table'])
            ->where('id_order = ' . $idOrder)
            ->where('data_collector = "order.create"');

        $row = Db::getInstance()->getRow($sql);
        if (empty($row)) {
            return [];
        }

        $decoded = json_decode($row['data']);
        if (empty($decoded)) {
            return [];
        }

        return (array)$decoded;
    }
}
