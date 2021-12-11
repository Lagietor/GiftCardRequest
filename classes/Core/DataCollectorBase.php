<?php

namespace Gcr\Core;

use Gcr\Core\DataCollectorInterface;
use Gcr\DataCollector\OrderCreateCollector;
use Gcr\DataCollector\OrderPaidCollector;
use \Order;
use \PrestaShop\PrestaShop\Adapter\MailTemplate\MailPartialTemplateRenderer;

abstract class DataCollectorBase implements DataCollectorInterface
{
    /** @var \Context */
    protected $context;

    /** @var int */
    protected $defaultIdLang;

    /** @var int */
    protected $idOrder;

    /** @var Order */
    protected $order;

    // TODO: opisać to
    /** @var array */
    protected $realProducts = [];

    /** @var MailPartialTemplateRenderer */
    protected $templateRenderer;

    /** @var array */
    protected $cartRules;

    public function __construct(int $idOrder)
    {
        $this->context = \Context::getContext();
        $this->idOrder = $idOrder;
        $this->order = new Order($idOrder);
        $this->defaultIdLang = $this->context->language->id;
        $this->cartRules = $this->order->getCartRules();
    }

    /**
     * Get info about DataCollectors for config form
     */
    public static function getAllQuery(): array
    {
        return [
            [
                'id_option' => 'order.create',
                'name' => 'New order',
            ],
            [
                'id_option' => 'order.paid',
                'name' => 'Order paid',
            ],
        ];
    }

    public static function getDataCollector(string $name, int $idOrder): ?DataCollectorInterface
    {
        switch ($name) {
            case 'order.create':
                return new OrderCreateCollector($idOrder);
                break;

            case 'order.paid':
                return new OrderPaidCollector($idOrder);
                break;

            default:
                return null;
                break;
        }
    }

    public function getUserIP(): string
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = (string)($_SERVER['HTTP_CLIENT_IP']);
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = (string)($_SERVER['HTTP_X_FORWARDED_FOR']);
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = (string)($_SERVER['HTTP_X_FORWARDED']);
        } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ipaddress = (string)($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']);
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = (string)($_SERVER['HTTP_FORWARDED_FOR']);
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = (string)($_SERVER['HTTP_FORWARDED']);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = (string)($_SERVER['REMOTE_ADDR']);
        } else {
            $ipaddress = '';
        }
        return $ipaddress;
    }

    /**
     * Read product from database (or from cache if enabled).
     * $order->getProducts() does not return all the necessary information.
     */
    protected function getRealProduct(int $idProduct): ?\Product
    {
        if (! isset($this->realProducts[$idProduct])) {
            $product = new \Product($idProduct);

            if (\Validate::isLoadedObject($product)) {
                $this->realProducts[$idProduct] = $product;
            } else {
                $this->realProducts[$idProduct] = null;
            }
        }


        return $this->realProducts[$idProduct];
    }

    protected function getMessage(int $idOrder, int $idState, int $idLang)//: string
    {
        $message = '';
        $orderState = new \OrderState($idState);
        $template = $orderState->template[$idLang]; // TODO: usunąć. wartość to np. 'bankwire'
        if (empty($template)) {
            return $message;
        }

        $idShop = \Context::getContext()->shop->id;
        $shop = new \Shop($idShop);
        $iso = \Language::getIsoById((int) $idLang);
        $isoDefault = \Language::getIsoById((int) \Configuration::get('PS_LANG_DEFAULT'));
        $isoArray = [];
        if ($iso) {
            $isoArray[] = $iso;
        }

        if ($isoDefault && $iso !== $isoDefault) {
            $isoArray[] = $isoDefault;
        }

        if (!in_array('en', $isoArray)) {
            $isoArray[] = 'en';
        }

        $templateExists = false;
        foreach ($isoArray as $isoCode) {
            $isoTemplate = $isoCode . '/' . $template;
            $templatePath = $this->getTemplateBasePath($isoTemplate, $shop->theme);

            if (file_exists($templatePath . $isoTemplate . '.txt')) {
                $templateExists = true;
                break;
            }
        }

        // TODO: fix - ogarnąć całą metodę
        if (! $templateExists) {
            dump('brak templatki'); die;
        }


        $templateVars = $this->getDefaultTemplateVars($idLang);

        $templateTxt = strip_tags(
            html_entity_decode(
                \Tools::file_get_contents($templatePath . $isoTemplate . '.txt'),
                0,
                'utf-8'
            )
        );
        // dump($templateTxt); die;

        $message = strtr($templateTxt, $templateVars);
        dump($message); die;
    }

    protected function getTemplateBasePath($isoTemplate, $theme): string
    {
        $basePathList = [
            _PS_ROOT_DIR_ . '/themes/' . $theme->getName() . '/',
            _PS_ROOT_DIR_ . '/themes/' . $theme->get('parent') . '',
            _PS_ROOT_DIR_,
        ];

        $templateRelativePath = '/mails/';

        foreach ($basePathList as $base) {
            $templatePath = $base . $templateRelativePath;
            if (file_exists($templatePath . $isoTemplate . '.txt')) {
                return $templatePath;
            }
        }

        return '';
    }

    protected function getDefaultTemplateVars(int $idLang): array
    {
        $templateVars = [
            '{shop_name}' => \Tools::safeOutput(\Configuration::get('PS_SHOP_NAME')),
            '{shop_url}' => \Context::getContext()->link->getPageLink(
                'index',
                true,
                $idLang
            ),
            '{my_account_url}' => \Context::getContext()->link->getPageLink(
                'my-account',
                true,
                $idLang
            ),
            '{guest_tracking_url}' => \Context::getContext()->link->getPageLink(
                'guest-tracking',
                true,
                $idLang
            ),
            '{history_url}' => \Context::getContext()->link->getPageLink(
                'history',
                true,
                $idLang
            ),
            '{order_slip_url}' => \Context::getContext()->link->getPageLink(
                'order-slip',
                true,
                $idLang
            ),
            '{color}' => \Tools::safeOutput(\Configuration::get('PS_MAIL_COLOR', $idLang)),
        ];

        $extraTemplateVars = [];
        \Hook::exec(
            'actionGetExtraMailTemplateVars',
            [
                'template' => 'bankwire',
                'template_vars' => $templateVars,
                'extra_template_vars' => &$extraTemplateVars,
                'id_lang' => $idLang,
            ],
            null,
            true
        );
        $templateVars = array_merge($templateVars, $extraTemplateVars);

        // TODO: dodatkowe pola?

        return $templateVars;
    }

    protected function getTemplateRenderer()
    {
        if (empty($this->templateRenderer)) {
            $this->templateRenderer = new MailPartialTemplateRenderer($this->context->smarty);
        }

        return $this->templateRenderer;
    }

    protected function getProductsTxt(array $productVarTplList)
    {
        $templateRenderer = $this->getTemplateRenderer();

        return $templateRenderer->render('order_conf_product_list.txt', $this->context->language, $productVarTplList);
    }

    protected function getProductsHtml(array $productVarTplList)
    {
        $templateRenderer = $this->getTemplateRenderer();

        return $templateRenderer->render('order_conf_product_list.tpl', $this->context->language, $productVarTplList);
    }

    protected function getProductVarTplList()
    {
        $productVarTplList = [];

        foreach ($this->order->getProducts() as $product) {
            $price = \Product::getPriceStatic(
                (int) $product['id_product'],
                false,
                ($product['id_product_attribute'] ? (int) $product['id_product_attribute'] : null),
                6,
                null,
                false,
                true,
                $product['cart_quantity'],
                false,
                (int) $this->order->id_customer,
                (int) $this->order->id_cart,
                (int) $this->order->{\Configuration::get('PS_TAX_ADDRESS_TYPE')},
                $specific_price,
                true,
                true,
                null,
                true,
                $product['id_customization']
            );

            $price_wt = \Product::getPriceStatic(
                (int) $product['id_product'],
                true,
                ($product['id_product_attribute'] ? (int) $product['id_product_attribute'] : null),
                2,
                null,
                false,
                true,
                $product['cart_quantity'],
                false,
                (int) $this->order->id_customer,
                (int) $this->order->id_cart,
                (int) $this->order->{\Configuration::get('PS_TAX_ADDRESS_TYPE')},
                $specific_price,
                true,
                true,
                null,
                true,
                $product['id_customization']
            );

            $product_price = \Product::getTaxCalculationMethod() == PS_TAX_EXC
                ? \Tools::ps_round($price, \Context::getContext()->getComputingPrecision())
                : $price_wt;

            $product_var_tpl = [
                'id_product' => $product['id_product'],
                'id_product_attribute' => $product['id_product_attribute'],
                'reference' => $product['reference'],
                'name' => $product['product_name'] . (isset($product['attributes'])
                    ? ' - ' . $product['attributes']
                    : ''),
                'price' => \Tools::getContextLocale($this->context)->formatPrice(
                    $product_price * $product['product_quantity'],
                    $this->context->currency->iso_code
                ),
                'quantity' => $product['product_quantity'],
            ];

            if (isset($product['price']) && $product['price']) {
                $product_var_tpl['unit_price'] = \Tools::getContextLocale($this->context)->formatPrice(
                    $product_price,
                    $this->context->currency->iso_code
                );
                $product_var_tpl['unit_price_full'] = \Tools::getContextLocale($this->context)->formatPrice(
                    $product_price,
                    $this->context->currency->iso_code
                    )
                    . ' ' . $product['unity'];
            } else {
                $product_var_tpl['unit_price'] = $product_var_tpl['unit_price_full'] = '';
            }

            $productVarTplList[] = $product_var_tpl;
        }

        return $productVarTplList;
    }
}
