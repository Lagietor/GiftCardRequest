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

    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get(_DB_PREFIX_ . 'BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitGiftCardRequestModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->tpl_var = [
            'field_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    protected function getConfigForm()
    {
        return [
            'form' => [
                'title' => $this->l('Settings'),
                'icon' => 'icon_cogs',
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->l('Module status'),
                    'name' => self::CONFIG_STATUS,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Enabled')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Disabled')
                        ]
                     ],
                ],
            ],
        ],
        'submit' => [
            'title' => $this->l('Save'),
        ],
    ];
    }

}

