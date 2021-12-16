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

$sql = [];

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'giftcardrequest_data`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'giftcardrequest_webhook`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'giftcardrequest_webhook_order_state`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}