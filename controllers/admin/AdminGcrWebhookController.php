<?php

class AdminGcrWebhookController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'giftcardrequest_webhook';
        $this->className = 'GcrWebHook';
        $this->lang = false;
        // $this->deleted = false; // TODO: co to robi?
        $this->context = Context::getContext();

        parent::__construct();
    }

    public function renderList()
    {
        // TODO: dodać rowAction z historią
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->fields_list = array(
            'id_giftcardrequest_webhook' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25
            ),
            'url' => array(
                'title' => $this->l('URL'),
                'width' => 'auto',
            ),
            'secure_key' => array(
                'title' => $this->l('Key'),
                'width' => 'auto',
            ),

            // TODO: nie działa wł/wył po ajaxie
            'active' => array(
                'title' => $this->l('Active'),
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'ajax' => true,
                'orderby' => false
            ),
        );

        $lists = parent::renderList();

        parent::initToolbar();

        return $lists;
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Example'),
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
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return parent::renderForm();
    }
}
