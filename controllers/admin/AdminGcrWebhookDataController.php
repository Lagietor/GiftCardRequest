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

use Gcr\WebhookSender;

class AdminGcrWebhookDataController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'giftcardrequest_data';
        $this->className = 'GcrRequestData';
        $this->lang = false;
        $this->context = Context::getContext();

        parent::__construct();

        $this->toolbar_title = sprintf(
            $this->l('WebHook - History (ID: %s)'),
            (int)(Tools::getValue('id_webhook'))
        );
    }

    public function init()
    {
        parent::init();

        $idWebhook = (int)Tools::getValue('id_webhook');

        if ($idWebhook) {
            $this->_where = ' AND id_webhook = ' . $idWebhook;
        }

        if (Tools::getValue('resend')) {
            $resendSuccess = 0;
            try {
                $this->resend((int)Tools::getValue('id_giftcardrequest_data'));
                $resendSuccess = 1;
            } catch (\Throwable $th) {
                PrestaShopLogger::addLog(
                    $this->module->name . ' - ' . $th->getMessage(),
                    PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING
                );
            }

            Tools::redirectAdmin(
                Context::getContext()->link->getAdminLink('AdminGcrWebhookData')
                . '&giftcardrequest_dataOrderby=date_upd'
                . '&giftcardrequest_dataOrderway=desc'
                . '&id_webhook=' . (int)Tools::getValue('id_webhook')
                . '&resend_success=' . $resendSuccess
            );
        }
    }

    public function initContent()
    {
        parent::initContent();

        $all = Tools::getAllValues();
        if (isset($all['resend_success'])) {
            if ($all['resend_success']) {
                $this->confirmations[] = $this->l('Webhook sent again');
            } else {
                $this->errors[] = $this->l('Resending webhook failed. See logs for details.');
            }
        }
    }

    private function resend(int $idGiftCardRequestData)
    {
        $reqData = new GcrRequestData($idGiftCardRequestData);

        $ws = new WebhookSender(
            new GcrWebHook($reqData->id_webhook),
            (int)$reqData->id_order
        );
        $ws->send();
    }

    public function displayResendLink($token, $id)
    {
        $tpl = $this->createTemplate('list_action_webhook_data_resend.tpl');

        $tpl->assign([
            'href' => $this->context->link->getAdminLink('AdminGcrWebhookData')
                . '&id_webhook=' . (int)Tools::getValue('id_webhook')
                . '&resend=1'
                . '&id_giftcardrequest_data=' . (int)$id,
            'action' => $this->l('Send')
        ]);

        return $tpl->fetch();
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['back_to_list'] = [
                'href' => $this->context->link->getAdminLink('AdminGcrWebhook'),
                'desc' => $this->l('Back to the list'),
                'icon' => 'process-icon-back'
            ];
        }

        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        unset($this->toolbar_btn['new']);

        $this->addRowAction('resend');

        $this->fields_list = [
            'id_giftcardrequest_data' => [
                'title' => $this->l('ID'),
                'width' => 'auto',
                'search' => false,
                'remove_onclick' => true
            ],
            'date_upd' => [
                'title' => $this->l('Date'),
                'width' => 'auto',
                'type' => 'datetime',
                'search' => false,
                'remove_onclick' => true
            ],
            'url' => [
                'title' => $this->l('URL'),
                'width' => 'auto',
                'search' => false,
                'remove_onclick' => true
            ],
            'data_collector' => [
                'title' => $this->l('Data Collector'),
                'width' => 'auto',
                'search' => false,
                'remove_onclick' => true
            ],
            'checksum' => [
                'title' => $this->l('Checksum'),
                'width' => 'auto',
                'search' => false,
                'remove_onclick' => true
            ],
            'http_response_code' => [
                'title' => $this->l('Response'),
                'width' => 'auto',
                'search' => false,
                'remove_onclick' => true
            ]
        ];

        $lists = parent::renderList();

        parent::initToolbar();

        return $lists;
    }
}
