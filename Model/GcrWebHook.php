<?php

class GcrWebHook extends ObjectModel
{
    /** @var string */
    private const TABLE_WEBHOOK_STATE = 'giftcardrequest_webhook_order_state';

    /** @var int */
    public $id;

    /** @var string */
    public $url;

    /** @var string */
    public $secure_key;

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
                    'min' => 1,
                    'max' => 255
                ],
            ],
            'secure_key' => [
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isGenericName',
                'size' => [
                    'min' => 1,
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

    public function setOrderStates(array $idsOrderStates): bool
    {
        return $this->removeOrderStates()
            && $this->storeOrderStates($idsOrderStates);
    }

    private function removeOrderStates(): bool
    {
        if (! $this->id) {
            return true;
        }

        return \Db::getInstance()->delete(self::TABLE_WEBHOOK_STATE, 'id_giftcardrequest_webhook = ' . (int)$this->id);
    }

    private function storeOrderStates(array $idsOrderStates = [])
    {
        if (empty($idsOrderStates)) {
            return true;
        }

        $insertValues = [];

        foreach ($idsOrderStates as $id) {
            $insertValues[] = [
                'id_giftcardrequest_webhook' => $this->id,
                'id_order_state' => $id,
            ];
        }

        return \Db::getInstance()->insert(self::TABLE_WEBHOOK_STATE, $insertValues);
    }

    public function getOrderStates(): array
    {
        if (empty($this->id)) {
            return [];
        }

        $sql = new \DbQuery();
        $sql->select('id_order_state')
            ->from(self::TABLE_WEBHOOK_STATE)
            ->where('id_giftcardrequest_webhook = ' . (int)$this->id);

        return array_column(\Db::getInstance()->executeS($sql), 'id_order_state');
    }
}
