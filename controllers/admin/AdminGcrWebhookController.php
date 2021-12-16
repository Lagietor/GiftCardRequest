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

class AdminGcrWebhookController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'giftcardrequest_webhook';
        $this->className = 'GcrWebHook';
        $this->lang = false;
        $this->context = Context::getContext();

        parent::__construct();

        $this->list_simple_header = true;
        $this->bulk_actions = [];
    }

    public function displayHistoryLink($token, $id)
    {
        $tpl = $this->createTemplate('list_action_webhook_history.tpl');

        $tpl->assign([
            'href' => $this->context->link->getAdminLink('AdminGcrWebhookData')
                . '&giftcardrequest_dataOrderby=date_upd'
                . '&giftcardrequest_dataOrderway=desc'
                . '&id_webhook=' . (int)$id,
            'action' => $this->l('History')
        ]);

        return $tpl->fetch();
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['foo'] = [
                'href' => $this->context->link->getAdminLink('AdminGcrWebhook')
                    . '&addgiftcardrequest_webhook',
                'desc' => $this->l('Add new webhook'),
                'icon' => 'process-icon-plus'
            ];
        }

        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('history');
        $this->addRowAction('delete');

        $this->fields_list = [
            'id_giftcardrequest_webhook' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25,
            ],
            'url' => [
                'title' => $this->l('URL'),
                'width' => 'auto',
            ],
            'secure_key' => [
                'title' => $this->l('Key'),
                'width' => 'auto',
            ],
            'data_collector' => [
                'title' => $this->l('Data Collector'),
                'width' => 'auto',
            ],
            'active' => [
                'title' => $this->l('Active'),
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'ajax' => false,
                'orderby' => false,
            ],
        ];

        $lists = parent::renderList();

        parent::initToolbar();

        return $lists;
    }

    public function renderForm()
    {
        /** @var \GcrWebHook $obj */
        $obj = $this->loadObject(true);

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('WebHook'),
                'icon' => 'icon-edit',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('URL:'),
                    'name' => 'url',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Key:'),
                    'name' => 'secure_key',
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'required' => false,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ]
                    ]
                ],
                [
                    'type' => 'swap',
                    'label' => $this->l('States'),
                    'desc' => $this->l(
                        'The webhook will be launched after the order status changes to the selected states'
                    ),
                    'name' => 'ids_order_states',
                    'required' > true,
                    'size' => 15,
                    'options' => array(
                        'query' => OrderState::getOrderStates($this->context->language->id),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ),
                ],
                [
                    'type'          => 'select',
                    'label'         => $this->l('Data collector:'),
                    'name'          => 'data_collector',
                    'multiple'      => false,
                    'required'      => true,
                    'empty_message' => $this->l('Nothing to display'),

                    'options' => [
                        'query' => Gcr\Core\DataCollectorBase::getAllQuery(),
                        'id'    => 'id_option',
                        'name'  => 'name',
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $this->fields_value['ids_order_states'] = $obj->getOrderStates();

        return parent::renderForm();
    }

    public function postProcess()
    {
        $parentResult = parent::postProcess();

        if (Tools::isSubmit('submitAddgiftcardrequest_webhook')) {
            /** @var \GcrWebHook $obj */
            if (! $obj = $this->loadObject()) {
                return $parentResult;
            }

            $idsOrderStates = Tools::getValue('ids_order_states_selected', []);
            $obj->setOrderStates($idsOrderStates);
        }

        return $parentResult;
    }
}
