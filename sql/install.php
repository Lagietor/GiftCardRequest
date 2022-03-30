<?php

/**
 * BonCard GiftCard Webhook Request.
 *
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 *
 * @package   Giftcard
 * @version   1.0.2
 * @copyright Copyright (c) 2021 BonCard Polska Sp. z o.o. (https://www.boncard.pl)
 * @license http://opensource.org/licenses/GPL-3.0 Open Software License (GPL 3.0)
 */

$sql = [];

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcardrequest_data` (
    `id_giftcardrequest_data` int NOT NULL AUTO_INCREMENT,
    `id_order` int NOT NULL,
    `id_webhook` int NOT NULL,
    `url` varchar(1000) NOT NULL,
    `data_collector` varchar(100) NOT NULL,
    `checksum` char(40) NOT NULL,
    `data` text NOT NULL,
    `http_response_code` int DEFAULT NULL,
    `date_add` datetime NOT NULL DEFAULT "2000-01-01 00:00:00",
    `date_upd` datetime NOT NULL DEFAULT "2000-01-01 00:00:00",
    PRIMARY KEY (`id_giftcardrequest_data`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcardrequest_webhook` (
    `id_giftcardrequest_webhook` int NOT NULL AUTO_INCREMENT,
    `url` varchar(255) NOT NULL,
    `secure_key` varchar(255) NOT NULL,
    `data_collector` varchar(50) NOT NULL,
    `active` tinyint NOT NULL DEFAULT "0",
    `date_add` datetime NOT NULL DEFAULT "2000-01-01 00:00:00",
    `date_upd` datetime NOT NULL DEFAULT "2000-01-01 00:00:00",
    PRIMARY KEY (`id_giftcardrequest_webhook`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcardrequest_webhook_order_state` (
    `id_giftcardrequest_webhook` int NOT NULL,
    `id_order_state` int NOT NULL,
    PRIMARY KEY (`id_giftcardrequest_webhook`,`id_order_state`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}