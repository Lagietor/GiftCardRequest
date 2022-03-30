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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_2($object)
{
    $success = true;

    $tabData = new Tab();
    $langs = Language::getLanguages();
    foreach ($langs as $lang) {
        $tabData->name[$lang['id_lang']] = $object->l('WebHooks Run For Existing');
    }
    $tabData->class_name = 'AdminGcrWebhookExisting';
    $tabData->id_parent = -1;
    $tabData->module = $object->name;
    $success &= $tabData->add();

    $success &= $object->unregisterHook('displayAdminOrderMainBottom');

    return $success;
}
