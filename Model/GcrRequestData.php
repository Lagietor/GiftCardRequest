<?php

/**
 * BonCard GiftCard Webhook Request.
 *
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 *
 * @package   Giftcard
 * @version   1.0.0
 * @copyright Copyright (c) 2021 BonCard Polska Sp. z o.o. (https://www.boncard.pl)
 * @license http://opensource.org/licenses/GPL-3.0 Open Software License (GPL 3.0)
 */

class GcrRequestData extends \ObjectModel
{
    /** @var int */
    public $id_giftcardrequest_data;

    /** @var int */
    public $id_order;

    /** @var int */
    public $id_webhook;

    /** @var string */
    public $url;

    /** @var string */
    public $data_collector;

    /** @var string */
    public $data;

    /** @var int */
    public $http_response_code;

    /** @var string */
    public $checksum;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

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
            'url' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isUrl',
                'required' => true,
            ],
            'data_collector' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'data' => [
                'type' => self::TYPE_STRING,
                'required' => true,
            ],
            'http_response_code' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'checksum' => [
                'type' => self::TYPE_STRING,
                'size' => [
                    'min' => 40,
                    'max' => 40,
                ],
                'required' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
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
        if (! Validate::isLoadedObject($webhook)) {
            throw new \Exception('Could not load Webhook - ID: ' . $this->id_webhook);
        }

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
