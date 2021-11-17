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
        // TODO: usunąć komentarz
        //! zazwyczaj w kontrolerze tak się nazywa ten obiekt, ale nie jest to żaden wymóg
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
