<?php

class GcrRequestData extends \ObjectModel
{
    /** @var int */
    public $id_giftcardrequest_data;

    /** @var int */
    public $id_order;

    /** @var string */
    public $data_collector;

    /** @var string */
    public $data;

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
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];
}
