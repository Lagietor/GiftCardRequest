<?php

class GcrWebHook extends ObjectModel
{
    /** @var int */
    public $id;

    /** @var string */
    public $url;

    /** @var string */
    public $secure_key;

    // TODO: skojarzyć z eventami

    // TODO: format potrzebny? jest jakiś inny niż JSON

    /** @var int */
    public $active;

    /** @var string Format Y-m-d H:i:s */
    public $date_add;

    /** @var string Format Y-m-d H:i:s */
    public $date_upd;

    /** @var array */
    public static $definition = [
        'table' => 'giftcardrequest_webhook',
        'primary' => 'id_giftcardrequest_webhook',
        'fields' => [
            'url' => [
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isUrl',
                'size' => [
                    'max' => 255
                ],
            ],
            'secure_key' => [
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isGenericName',
                'size' => [
                    'max' => 255
                ],
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'required' => true,
                'validate' => 'isBool',
                'active' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];
}
