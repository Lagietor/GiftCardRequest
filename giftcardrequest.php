<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class GiftCardRequest extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'giftcardrequest';
        $this->tab = 'back_office_features';
        $this->version = "0.1";
        $this->author = "Rej & Lagietor";
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('My Module of GiftCard Requests');
        $this->description = $this->l('WIth this module you will be able to send requests if a customer uses a gift card');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module? (GiftCardRequest');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        //Configuration::updateValue();

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        //Configuration::deleteByName();

        return true;
    }

    public function getContent()
    {
        return $this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->table = 'giftcardrequest';
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get(_DB_PREFIX_ . 'BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->submit_action = 'giftCardRequestSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->tpl_vars = [
            'fields_value' => [
                'enableModule' => Tools::getValue('enableModule', 1)//Configuration::get('ENABLE_MODULE'))
            ],
            'languages' => $this->context->controller->getLanguages()
        ];

        return $helper->generateForm([$this->Form()]);
    }

    protected function form()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuration'),
                    'icon' => 'icon-wrench'
                ],
                'input' => [
                    [
                    'type' => 'switch',
                    'label' => $this->l('Enable module'),
                    'name' => 'enableModule',
                    'values' => [
                        [
                            'id' => 'enableModule1',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ],
                        [
                            'id' => 'enableModule0',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ]
                    ]
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save')
                ]
            ]
        ];
    }
}
