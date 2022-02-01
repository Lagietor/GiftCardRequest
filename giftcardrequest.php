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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(__DIR__ . '/vendor/autoload.php');

class GiftCardRequest extends Module
{
    protected $config_form = false;

    private const CONFIG_STATUS = 'GIFTCARDREQUESTMODULE_STATUS';
    private const CONFIG_STATUS_DEFAULT = 1;

    public function __construct()
    {
        $this->name = 'giftcardrequest';
        $this->tab = 'back_office_features';
        $this->version = '1.0.0';
        $this->author = 'Giftcard';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Webhook Module');
        $this->description = $this->l('Sends webhooks with information about the order');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        if (! parent::install() || ! $this->installTabs()) {
            return false;
        }

        $this->registerHook('actionOrderStatusPostUpdate');

        Configuration::updateValue(self::CONFIG_STATUS, self::CONFIG_STATUS_DEFAULT);

        return true;
    }

    private function installTabs(): bool
    {
        $success = true;

        $tabWebHooks = new Tab();
        $langs = Language::getLanguages();
        foreach ($langs as $lang) {
            $tabWebHooks->name[$lang['id_lang']] = $this->l('WebHooks');
        }
        $tabWebHooks->class_name = 'AdminGcrWebhook';
        $tabWebHooks->id_parent = -1;
        $tabWebHooks->module = $this->name;
        $success &= $tabWebHooks->add();

        $tabData = new Tab();
        $langs = Language::getLanguages();
        foreach ($langs as $lang) {
            $tabData->name[$lang['id_lang']] = $this->l('WebHooks Data');
        }
        $tabData->class_name = 'AdminGcrWebhookData';
        $tabData->id_parent = -1;
        $tabData->module = $this->name;
        $success &= $tabData->add();

        return $success;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        $this->uninstallTabs();
        Configuration::deleteByName(self::CONFIG_STATUS);

        include(dirname(__FILE__).'/sql/uninstall.php');

        return true;
    }

    private function uninstallTabs(): void
    {
        $tabs = Tab::getCollectionFromModule($this->name);
        foreach ($tabs as $moduleTab) {
            $moduleTab->delete();
        }
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminGcrWebhook'));
        die;
    }

    public function getHookController($hookName): \Gcr\Core\HookControllerInterface
    {
        try {
            $controllerName = $hookName . 'Controller';
            $path = __DIR__ . '/controllers/hooks/' . $controllerName . '.php';

            if (! file_exists($path)) {
                throw new \Exception('Hook controller not found: ' . $path);
            }

            require_once($path);

            return new $controllerName($this);
        } catch (\Throwable $th) {
            echo $th->getMessage();
            die;
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        try {
            $controller = $this->getHookController('ActionOrderStatusPostUpdate');
            return $controller->run($params);
        } catch (\Throwable $th) {
            PrestaShopLogger::addLog(
                $this->name . ' - ' . $th->getMessage(),
                PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING
            );
        }
    }
}
