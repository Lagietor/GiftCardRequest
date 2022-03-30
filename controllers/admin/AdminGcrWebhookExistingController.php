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

class AdminGcrWebhookExistingController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->context = Context::getContext();

        parent::__construct();

        $this->list_simple_header = true;
        $this->bulk_actions = [];
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['list'] = [
                'href' => $this->context->link->getAdminLink('AdminGcrWebhook'),
                'desc' => $this->l('Back to list'),
                'icon' => 'icon-arrow-left'
            ];
        }

        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        return $this->renderForm();
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('WebHook'),
                'icon' => 'icon-edit',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Order IDs:'),
                    'required' => true,
                    'name' => 'order_ids',
                    'desc' => $this->l('Identifiers separated by commas'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Webhook:'),
                    'name' => 'id_webhook',
                    'multiple' => false,
                    'required' => true,
                    'empty_message' => $this->l('Nothing to display'),

                    'options' => [
                        'query' => \GcrWebHook::getAllQuery(),
                        'id'    => 'id_option',
                        'name'  => 'name',
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Send'),
            ],
        ];

        if (Tools::getValue('done')) {
            $this->confirmations[] = 'Done';
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (! Tools::isSubmit('submitAddconfiguration')) {
            return;
        }

        try {
            $orderIdsQuery = \Tools::getValue('order_ids');
            $orderIds = explode(',', $orderIdsQuery);

            if (empty($orderIds)) {
                throw new \Exception($this->l('Order IDs could not be recognized'));
            }

            $orderIds = array_filter($orderIds, function($el) {
                return is_numeric($el) && $el > 0;
            });

            $orderIds = array_map('intval', $orderIds);

            if (empty($orderIds)) {
                throw new \Exception($this->l('Order IDs could not be recognized'));
            }

            $idWebhook = (int)(\Tools::getValue('id_webhook'));
            $webhook = new \GcrWebHook($idWebhook);
            if (! \Validate::isLoadedObject($webhook)) {
                throw new \Exception($this->l('Webhok not found'));
            }

            foreach ($orderIds as $idOrder) {
                $webhookSender = new Gcr\WebhookSender(
                    new GcrWebHook($idWebhook),
                    $idOrder
                );
                $webhookSender->send();
            }

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminGcrWebhookExisting') . '&done=1');
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();
        }

    }
}
