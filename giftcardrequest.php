<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class GiftCardRequest extends Module
{
    protected $config_form = false;

    private const CONFIG_STATUS = 'GIFTCARDREQUESTMODULE_STATUS';
    private const CONFIG_STATUS_DEFAULT = 1;
    private const CONFIG_URL_FIELD = 'GIFTCARDREQUEST_URL_FIELD';
    private const CONFIG_KEY_FIELD = 'GIFTCARDREQUEST_KEY_FIELD';
    private const CONFIG_FORM = "GIFTCARDREQUEST_FORM";
    private const CONFIG_EVENT = "GIFTCARDREQUEST_EVENT";

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
        $this->description = $this->l('With this module you will be able to send requests if a customer uses a gift card');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module? (GiftCardRequest)');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $this->registerHook('actionOrderStatusPostUpdate');
        $this->registerHook('actionObjectOrderAddAfter');
        $this->registerHook('Header');

        Configuration::updateValue(self::CONFIG_STATUS, self::CONFIG_STATUS_DEFAULT);

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        Configuration::deleteByName(self::CONFIG_STATUS);

        return true;
    }

    public function getContent()
    {
        $this->output = '';
        if (((bool)Tools::isSubmit('giftCardRequestSubmit')) == true) {
            $this->postProcess();
        }

        //return $this->confTable();
        return $this->output . $this->renderForm() . $this->confTable();
    }

    public function getHookController($hookName)
    {
        require_once(__DIR__ . '/controllers/hooks/' . $hookName . '.php');
        $controllerName = $hookName . 'Controller';
        $controller = new $controllerName($this);

        return $controller;
    }

    public function hookHeader($params) // hook do testów
    {
        // $controller = $this->getHookController('header');
        // return $controller->run($params);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $controller = $this->getHookController('ActionOrderStatusPostUpdate');
        return $controller->run($params);
    }

    public function hookActionObjectOrderAddAfter($params)
    {
        // $controller = $this->getHookController('ActionObjectOrderAddAfter');
        // return $controller->run($params);
    }

    protected function postProcess(): void
    {
        if (
            Configuration::updateValue(self::CONFIG_STATUS, (int)Tools::getValue(self::CONFIG_STATUS)) &&
            Configuration::updateValue(self::CONFIG_URL_FIELD, (string)Tools::getValue(self::CONFIG_URL_FIELD)) &&
            Configuration::updateValue(self::CONFIG_KEY_FIELD, (string)Tools::getValue(self::CONFIG_KEY_FIELD)) &&
            Configuration::updateValue(self::CONFIG_FORM, (string)Tools::getValue(self::CONFIG_FORM)) &&
            Configuration::updateValue(self::CONFIG_EVENT, (string)Tools::getValue(self::CONFIG_EVENT))
        ) {
            $this->output .= $this->displayConfirmation('Saved');
        } else {
            $this->output .= $this->displayError('Save has failed');
        }
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->table = 'giftcardrequest';
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get(_DB_PREFIX_ . 'BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->submit_action = 'giftCardRequestSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->tpl_vars = [
            'fields_value' => [
                self::CONFIG_STATUS => Tools::getValue(self::CONFIG_STATUS, true),
                self::CONFIG_URL_FIELD => Tools::getValue(self::CONFIG_URL_FIELD),
                self::CONFIG_KEY_FIELD => Tools::getValue(self::CONFIG_KEY_FIELD),
                self::CONFIG_FORM => Tools::getValue(self::CONFIG_FORM),
                self::CONFIG_EVENT => Tools::getValue(self::CONFIG_EVENT)
            ],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
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
                    'label' => $this->l('Module status'),
                    'name' => self::CONFIG_STATUS,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'enableModule1',
                            'value' => true,
                            'label' => $this->l('Enabled')
                        ],
                        [
                            'id' => 'enableModule0',
                            'value' => false,
                            'label' => $this->l('Disabled')
                        ]
                    ]
                    ],
                    [
                        'type' => 'text',
                        'name' => self::CONFIG_URL_FIELD,
                        'label' => $this->l('Adres URL: '),
                        'required' => true
                    ],
                    [
                        'type' => 'text',
                        'name' => self::CONFIG_KEY_FIELD,
                        'label' => $this->l('Klucz: '),
                    ],
                    [
                        'type' => 'select',
                        'name' => self::CONFIG_FORM,
                        'label' => $this->l('Format: '),
                        'options' => [
                            'query' => [
                                [
                                    'id_option' => 1,
                                    'name' => 'JSON'
                                ],
                                [
                                    'id_option' => 2,
                                    'name' => 'JDAD'
                                ],
                                [
                                    'id_option' => 3,
                                    'name' => 'JMOM'
                                ]
                            ],
                            'name' => 'name',
                            'id' => 'id_option'
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Zdarzenia: '),
                        'name' => self::CONFIG_EVENT,
                        'multiple' => true,
                        'options' => [
                            'query' => [
                                ['key' => '1', 'name' => 'order.create'],
                                ['key' => '2', 'name' => 'order.paid'],
                                        ],
                            'id' => 'key',
                            'name' => 'name'
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save')
                ],
            ],
        ];
    }

    public function confTable()
    {
        $fields_list = [
            'order_id' => [
                'title' => $this->l('Id'),
                'width' => 140
                //'type' => 'float',
            ],
            'date' => [
                'title' => $this->l('Data'),
                'width' => 140,
                //'type' => 'date',
            ],
            'URL' => [
                'title' => $this->l('URL'),
                'width' => 140,
                //'type' => 'select'
            ],
            'Events' => [
                'title' => $this->l("Zdarzenia"),
                'width' => 140,
                //'type' => 'select'
            ],
            'Controlled sum' => [
                'title' => $this->l("Suma kontrolna"),
                'width' => 140,
                //'type' => 'select'
            ],
            'Status' => [
                'title' => $this->l("Status"),
                'width' => 140,
                //'type' => 'text'
            ],
            'send Again' => [
                'title' => $this->l('Wyślij ponownie'),
                'width' => 140,
                //'type' => 'button'
            ]
        ];

        $query = "SELECT email, notes FROM " . _DB_PREFIX_ . "ordercreatedata";
        $list = Db::getInstance()->executeS($query);
        print_r($list);

        $helper = new HelperList();

        $helper->shopLinkType = '';

        $helper->simple_header = true;

        $helper->actions = ['edit', 'delete', 'view'];

        $helper->identifier = 'order_id';
        $helper->show_toolbar = true;
        $helper->title = $this->l("Historia Requestów");
        $helper->table = '';

        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        return $helper->generateList($list, $fields_list);
    }
    //metoda do możliwego wykorzystania w przyszłości
    private function getConfigFormValues(): array
    {
        return [
            self::CONFIG_STATUS => Configuration::get(self::CONFIG_STATUS)
        ];
    }
}
