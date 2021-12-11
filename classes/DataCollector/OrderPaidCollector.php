<?php

namespace Gcr\DataCollector;

use Gcr\Core\DataCollectorBase;
use \Module;
use \Tools;
use \Customer;
use \DbQuery;
use \Order;
use \Carrier;
use \TaxRulesGroup;
use \Product;
use \Address;
use \Country;
use \State;
use \OrderState;
use \Db;
use \Configuration;
use \AddressFormat;
use \DateTime;
use \DateInterval;
use \Validate;
use \CartRule;

class OrderPaidCollector extends DataCollectorBase
{
    protected const PRECISION = 2;
    protected const STATUS_TYPE = 3; // 1 for new, 3 for paid
    protected const STATUS_EMAIL_CHANGE = 0;
    protected const PRODUCT_UNIT_FP = 0;
    protected const PAYMENT_DEFAULT = 'payu';

    protected const SHIPPING_DEPEND_ON_WEIGHT = 1;
    protected const SHIPPING_DEPEND_ON_AMOUNT = 2;
    protected const SHIPPING_DEPEND_ON_DEFAULT = 0;
    protected const FREE_SHIPPING_MIN_PROD_NB = 4;

    /** @var array Products in Order */
    protected $products = [];

    /** @var Customer */
    protected $customer;

    /** @var array */
    protected $orderStatusInfo;

    /** @var Carrier */
    protected $carrier;

    /** @var TaxRulesGroup */
    protected $taxRuleGroup;

    /** @var Address */
    protected $billingAddress;

    /** @var Address */
    protected $deliveryAddress;

    /** @var Country */
    protected $billingCountry;

    /** @var Country */
    protected $deliveryCountry;

    /** @var State */
    protected $billingState;

    /** @var State */
    protected $deliveryState;

    /** @var OrderState */
    protected $state;

    /** @var Module */
    protected $payment;

    protected function getOrderStatusInfo(): array
    {
        $sql = new DbQuery();
        $sql->select('id_order_state, date_add')
            ->from('order_history')
            ->orderBy('id_order_history DESC');

        return Db::getInstance()->getRow($sql);
    }

    protected function readProductsFromOrder(): void
    {
        $products = $this->order->getProducts();

        foreach ($products as $product) {
            $this->products[$product['id_product']] = $product;
        }
    }

    public function getData(): array
    {

        // TODO: walidować czy poprawnie pobrano, jak nie to wyjątek (zaimplementować wyjątki :P)

        $this->readProductsFromOrder();
        $this->customer = new Customer($this->order->id_customer);
        $this->orderStatusInfo = $this->getOrderStatusInfo();

        // TODO: potrzebne?
        // $product = new Product($this->order->getProductsDetail()[0]['id_product'], true, $this->order->id_lang);

        $this->carrier = new Carrier($this->order->id_carrier);
        $this->taxRuleGroup = new TaxRulesGroup(
            $this->carrier->getIdTaxRulesGroupByIdCarrier($this->order->id_carrier)
        );

        $this->billingAddress = new Address($this->order->id_address_invoice);
        $this->billingCountry = new Country($this->billingAddress->id);
        $this->billingState = $this->billingAddress->id_state
            ? new State((int) $this->billingAddress->id_state)
            : false;

        $this->deliveryAddress = new Address($this->order->id_address_delivery);
        $this->deliveryCountry = new Country($this->deliveryAddress->id);
        $this->deliveryState = $this->deliveryAddress->id_state
            ? new State((int) $this->deliveryAddress->id_state)
            : false;

        $this->state = new OrderState($this->order->current_state);
        $this->payment = Module::getInstanceById(Module::getModuleIdByName($this->order->module));

        $productVarTplList = $this->getProductVarTplList();

        $data = [
            'order_id' => $this->order->reference,
            'user_id' => $this->customer->id,
            'date' => $this->order->date_add,
            'status_date' => $this->orderStatusInfo['date_add'],
            'confirm_date' => $this->order->date_add,
            'delivery_date' => $this->getDeliveryDate(),
            'status_id' => $this->orderStatusInfo['id_order_state'],
            'sum' => round($this->order->total_paid_tax_incl, self::PRECISION),
            'payment_id' => Module::getModuleIdByName($this->order->module),
            'user_order' => 1, // only register users
            'shipping_id' => $this->order->id_carrier,
            'shipping_cost' => round($this->order->total_shipping_tax_incl, self::PRECISION),
            'email' => $this->customer->email,
            'delivery_code' => '',
            'code' => '',
            'confirm' => 0,
            'notes' => $this->order->getFirstMessage(),
            'notes_priv' => '',
            'notes_pub' => '',
            'currency_id' => $this->order->id_currency,
            'currency_name' => 'PLN', // only PLN supported
            'currency_rate' => 1,
            'paid' => 0.0, // TODO: dla nowego 0, dla opłaconego - sum
            'ip_address' => $this->getUserIP(), //TODO: dla aktualizacji płatności, pobrać IP z tworzenia

            'discount_client' => $this->getDiscountClient(),
            'discount_group' => $this->getPercentDiscountGroup(),
            'discount_levels' => 0,
            'discount_code' => 0,  // will be set later
            // TODO: ogarnąć podatki (chyba przy produktach)
            'shipping_vat' => $this->carrier->getIdTaxRulesGroupByIdCarrier($this->order->id_carrier),
            'shipping_vat_value' => round(
                $this->order->total_shipping_tax_incl - $this->order->total_shipping_tax_excl,
                self::PRECISION
            ),
            'shipping_vat_name' => $this->taxRuleGroup->name,
            'code_id' => $this->getCodeId(),
            'lang_id' => $this->order->id_lang,
            'origin' => 1,
            'promo_code' => $this->getPromoCode(),

            'billingAddress.address_id' => $this->billingAddress->id,
            'billingAddress.order_id' => $this->order->reference,
            'billingAddress.type' => 1,
            'billingAddress.firstname' => $this->billingAddress->firstname,
            'billingAddress.lastname' => $this->billingAddress->lastname,
            'billingAddress.company' => $this->billingAddress->company,
            'billingAddress.tax_id' => $this->billingAddress->var_number,
            'billingAddress.pesel' => isset($this->billingAddress->pesel) ? $this->billingAddress->pesel : '',
            'billingAddress.city' => $this->billingAddress->city,
            'billingAddress.postcode' => $this->billingAddress->postcode,
            'billingAddress.street1' => $this->billingAddress->address1,
            'billingAddress.street2' => $this->billingAddress->address2,
            'billingAddress.state' => $this->billingAddress->id_state ? $this->billingState->name : '',
            'billingAddress.country' => $this->billingAddress->country,
            'billingAddress.phone' => $this->billingAddress->phone_mobile
                ? $this->billingAddress->phone_mobile
                : $this->billingAddress->phone,
            'billingAddress.country_code' => $this->billingCountry->iso_code,

            'deliveryAddress.address_id' => $this->deliveryAddress->id,
            'deliveryAddress.order_id' => $this->order->reference,
            'deliveryAddress.type' => 2,
            'deliveryAddress.firstname' => $this->deliveryAddress->firstname,
            'deliveryAddress.lastname' => $this->deliveryAddress->lastname,
            'deliveryAddress.company' => $this->deliveryAddress->company,
            'deliveryAddress.tax_id' => $this->deliveryAddress->var_number,
            'deliveryAddress.pesel' => isset($this->deliveryAddress->pesel) ? $this->deliveryAddress->pesel : '',
            'deliveryAddress.city' => $this->deliveryAddress->city,
            'deliveryAddress.postcode' => $this->deliveryAddress->postcode,
            'deliveryAddress.street1' => $this->deliveryAddress->address1,
            'deliveryAddress.street2' => $this->deliveryAddress->address2,
            'deliveryAddress.state' => $this->deliveryAddress->id_state ? $this->deliveryState->name : '',
            'deliveryAddress.country' => $this->deliveryAddress->country,
            'deliveryAddress.phone' => $this->deliveryAddress->phone_mobile
                ? $this->deliveryAddress->phone_mobile
                : $this->deliveryAddress->phone,
            'deliveryAddress.country_code' => $this->deliveryCountry->iso_code,

            'shipping.shipping_id' => $this->carrier->id,
            'shipping.name' => $this->carrier->name,
            'shipping.description' => '',
            'shipping.cost' => round($this->order->total_shipping_tax_incl, self::PRECISION),
            'shipping.depend_on_w' => $this->getShippingDependOn(),
            'shipping.zone_id' => $this->deliveryCountry->id_zone,
            'shipping.tax_id' => $this->carrier->getIdTaxRulesGroupByIdCarrier($this->order->id_carrier),
            'shipping.max_weight' => (float)$this->carrier->max_weight,
            'shipping.min_weight' => 0.0,
            'shipping.free_shipping' => $this->isFreeShipping(), // TODO: wysłać zrzut
            'shipping.order' => '', // TODO: fix
            'shipping.is_default' => (int)($this->carrier->id == Configuration::get('PS_CARRIER_DEFAULT')),
            'shipping.pkwiu' => '',
            'shipping.mobile' => 1,
            'shipping.engine' => '',
            'shipping.callback_url' => $this->carrier->url, // TODO: fix :)

            'status.status_id' => $this->order->current_state,
            'status.default' => (int)($this->payment->name == self::PAYMENT_DEFAULT),
            'status.color' => $this->state->color,
            'status.type' => self::STATUS_TYPE,
            'status.email_change' => self::STATUS_EMAIL_CHANGE,
            'status.order' => '', // TODO: fix
            'status.name' => $this->state->name[$this->defaultIdLang],

            // 'message' => $this->getMessage($this->idOrder, $this->orderStatusInfo['id_order_state'], $this->defaultIdLang),

            'payment.payment_id' => $this->payment->id,
            'payment.order' => '', // TODO: todo
            'payment.name' => $this->payment->name,
            'payment.title' => $this->payment->displayName,
            'payment.description' => '', $this->payment->description,
            'payment.notify_mail' => '',
        ];

        // TODO: dane potrzebne do generowania pola message - przenieść
        $data1 = [
            '{firstname}' => $this->customer->firstname,
            '{lastname}' => $this->customer->lastname,
            '{email}' => $this->customer->email,
            '{delivery_block_txt}' => $this->getFormatedAddress($this->deliveryAddress, AddressFormat::FORMAT_NEW_LINE),
            '{invoice_block_txt}' => $this->getFormatedAddress($this->billingAddress, AddressFormat::FORMAT_NEW_LINE),
            '{delivery_block_html}' => $this->getFormatedAddress($this->deliveryAddress, '<br />', [
                'firstname' => '<span style="font-weight:bold;">%s</span>',
                'lastname' => '<span style="font-weight:bold;">%s</span>',
            ]),
            '{invoice_block_html}' => $this->getFormatedAddress($this->billingAddress, '<br />', [
                'firstname' => '<span style="font-weight:bold;">%s</span>',
                'lastname' => '<span style="font-weight:bold;">%s</span>',
            ]),
            '{delivery_company}' => $this->deliveryAddress->company,
            '{delivery_firstname}' => $this->deliveryAddress->firstname,
            '{delivery_lastname}' => $this->deliveryAddress->lastname,
            '{delivery_address1}' => $this->deliveryAddress->address1,
            '{delivery_address2}' => $this->deliveryAddress->address2,
            '{delivery_city}' => $this->deliveryAddress->city,
            '{delivery_postal_code}' => $this->deliveryAddress->postcode,
            '{delivery_country}' => $this->deliveryAddress->country,
            '{delivery_state}' => $this->deliveryAddress->id_state ? $this->deliveryState->name : '',
            '{delivery_phone}' => ($this->deliveryAddress->phone)
                ? $this->deliveryAddress->phone
                : $this->deliveryAddress->phone_mobile,
            '{delivery_other}' => $this->deliveryAddress->other,
            '{invoice_company}' => $this->billingAddress->company,
            '{invoice_vat_number}' => $this->billingAddress->vat_number,
            '{invoice_firstname}' => $this->billingAddress->firstname,
            '{invoice_lastname}' => $this->billingAddress->lastname,
            '{invoice_address2}' => $this->billingAddress->address2,
            '{invoice_address1}' => $this->billingAddress->address1,
            '{invoice_city}' => $this->billingAddress->city,
            '{invoice_postal_code}' => $this->billingAddress->postcode,
            '{invoice_country}' => $this->billingAddress->country,
            '{invoice_state}' => $this->billingAddress->id_state ? $this->billingState->name : '',
            '{invoice_phone}' => ($this->billingAddress->phone)
                ? $this->billingAddress->phone
                : $this->billingAddress->phone_mobile,
            '{invoice_other}' => $this->billingAddress->other,
            '{order_name}' => $this->order->getUniqReference(),
            '{id_order}' => $this->order->id,
            '{date}' => Tools::displayDate(date('Y-m-d H:i:s'), null, 1),
            '{carrier}' => ! isset($this->carrier->name) ? '' : $this->carrier->name,
            '{payment}' => Tools::substr($this->order->payment, 0, 255),
            '{products}' => $this->getProductsHtml($this->getProductVarTplList($this->order)),
            '{products_txt}' => $this->getProductsTxt($this->getProductVarTplList($this->order)),
            '{total_paid}' => Tools::getContextLocale($this->context)->formatPrice(
                $this->order->total_paid,
                $this->context->currency->iso_code
            ),
            '{total_products}' => Tools::getContextLocale($this->context)->formatPrice(
                Product::getTaxCalculationMethod() == PS_TAX_EXC
                    ? $this->order->total_products
                    : $this->order->total_products_wt,
                $this->context->currency->iso_code
            ),
            '{total_discounts}' => Tools::getContextLocale($this->context)->formatPrice(
                $this->order->total_discounts,
                $this->context->currency->iso_code
            ),
            '{total_shipping}' => Tools::getContextLocale($this->context)->formatPrice(
                $this->order->total_shipping,
                $this->context->currency->iso_code
            ),
            '{total_shipping_tax_excl}' => Tools::getContextLocale($this->context)->formatPrice(
                $this->order->total_shipping_tax_excl,
                $this->context->currency->iso_code
            ),
            '{total_shipping_tax_incl}' => Tools::getContextLocale($this->context)->formatPrice(
                $this->order->total_shipping_tax_incl,
                $this->context->currency->iso_code)
                ,
            '{total_wrapping}' => Tools::getContextLocale($this->context)->formatPrice(
                $this->order->total_wrapping,
                $this->context->currency->iso_code
            ),
            '{total_tax_paid}' => Tools::getContextLocale($this->context)->formatPrice(
                ($this->order->total_paid_tax_incl - $this->order->total_paid_tax_excl),
                $this->context->currency->iso_code
            ),
        ];


        // products info
        $this->getProducts($this->order, $data);

        // set 'discount_code' value
        if (! empty($this->products)) {
            $firstProduct = reset($this->products);
            $data['discount_code'] = floor($data['product' . (int)$firstProduct['id_product']]['discount_perc']);
        }

        $data['additional_fields'] = [];

        return $data;
    }

    /**
     * Return last cart rule ID for order.
     */
    protected function getCodeId(): ?int
    {
        if (empty($this->cartRules)) {
            return null;
        }

        $lastCartRule = end($this->cartRules);

        return (int)$lastCartRule['id_cart_rule'];
    }

    /**
     * Return last cart rule name for order.
     */
    protected function getPromoCode(): string
    {
        if (empty($this->cartRules)) {
            return '';
        }

        $lastCartRule = end($this->cartRules);

        return $lastCartRule['name'];
    }

    protected function getDiscountClient(): float
    {
        $sum = 0;
        foreach ($this->cartRules as $ruleInfo) {
            $cartRule = new CartRule((int)$ruleInfo['id_cart_rule']);
            if (! Validate::isLoadedObject($cartRule) || $cartRule->id_customer != $this->customer->id) {
                continue;
            }

            $sum += $ruleInfo['value'];
        }

        return round($sum, self::PRECISION);
    }

    protected function getFormatedAddress(Address $the_address, $line_sep, $fields_style = [])
    {
        return AddressFormat::generateAddress($the_address, ['avoid' => []], $line_sep, ' ', $fields_style);
    }

    protected function getProducts(Order $order, array &$data): void
    {
        $inOrder = $this->products;

        foreach ($inOrder as $p) {
            $idProduct = (int)$p['id_product'];
            $product = $this->getRealProduct($idProduct);

            $data["product$idProduct.id"] = $idProduct;
            $data["product$idProduct.order_id"] = $order->reference;
            $data["product$idProduct.product_id"] = $p['reference'];
            $data["product$idProduct.stock_id"] = ''; // TODO: todo
            $data["product$idProduct.price"] = $p['product_price_wt'];
            $data["product$idProduct.discount_perc"] = $this->getProductPercentDiscount($idProduct);
            $data["product$idProduct.quantity"] = $p['product_quantity'];
            $data["product$idProduct.delivery_time"] = $this->getDeliveryTime($idProduct);
            $data["product$idProduct.name"] = $p['product_name'];
            $data["product$idProduct.code"] = $product->reference;
            $data["product$idProduct.pkwiu"] = '';
            $data["product$idProduct.tax"] = $p['tax_name'];
            $data["product$idProduct.tax_value"] = round($p['product_price_wt'] - $p['product_price'], self::PRECISION);
            $data["product$idProduct.unit"] = (string)($product->unity);
            $data["product$idProduct.option"] = '';
            $data["product$idProduct.unit_fp"] = self::PRODUCT_UNIT_FP;
            $data["product$idProduct.weight"] = $p['weight'];
        }
    }

    protected function getDeliveryDate(): string
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $this->order->date_add);
        $date->add(new DateInterval('P' . $this->getMaxDeliveryTime() . 'D'));

        return $date->format('Y-m-d');
    }

    protected function getMaxDeliveryTime(): int
    {
        $deliveryTimes = [];

        foreach ($this->products as $product) {
            $deliveryTimes[] = $this->getDeliveryTime($product['id_product']);
        }

        return (int)(max($deliveryTimes));
    }

    protected function getDeliveryTime(int $idProduct): float
    {
        /** @var Product */
        $product = $this->products[$idProduct];
        if (empty($product)) {
            return 0.0;
        }

        if ($product['is_virtual']) {
            return 0.0;
        }

        if ($product['additional_delivery_times'] == 1) {
            $deliveryTime = Configuration::get('PS_LABEL_DELIVERY_TIME_AVAILABLE', $this->defaultIdLang);
        } elseif ($product['additional_delivery_times'] == 2) {
            $realProduct = $this->getRealProduct($idProduct);
            $deliveryTime = $realProduct->delivery_in_stock[$this->defaultIdLang];
        } else {
            $deliveryTime = 0;
        }

        return (float)$deliveryTime;
    }

    protected function getShippingDependOn(): int
    {
        $method = (int)($this->carrier->shipping_method);

        switch ($method) {
            case self::SHIPPING_DEPEND_ON_WEIGHT:
            case self::SHIPPING_DEPEND_ON_AMOUNT:
                return $method;
                break;

            default:
                return self::SHIPPING_DEPEND_ON_DEFAULT;
                break;
        }
    }

    protected function getProductPercentDiscount(int $idProduct): float
    {
        if (! isset($this->products[$idProduct])) {
            return 0.0;
        }

        $product = $this->products[$idProduct];

        if ((float)$product['reduction_percent']) {
            return round(
                (float)$product['reduction_percent'],
                self::PRECISION
            );
        } elseif ((float)isset($product['reduction_amount'])) {
            return round(
                abs((($product['product_price'] / $product['price']) - 1) * 100),
                self::PRECISION
            );
        } else {
            return 0.0;
        }
    }

    protected function getPercentDiscountGroup(): float
    {
        $firstProduct = reset($this->products);
        if (empty($firstProduct)) {
            return 0.0;
        }

        if ((float)$firstProduct['reduction_percent']) {
            return round(
                (float)$firstProduct['reduction_percent'],
                self::PRECISION
            );
        } elseif ((float)isset($firstProduct['reduction_amount'])) {
            return round(
                abs((($firstProduct['product_price'] / $firstProduct['price']) - 1) * 100),
                self::PRECISION
            );
        } else {
            return 0.0;
        }
    }

    /**
     * Free shipping when the order is virtual or if the minimum number of products has been reached.
     *
     * @return int 1 if free, or 0 if not
     */
    protected function isFreeShipping(): int
    {
        if ($this->order->isVirtual()
            || $this->countAllProducts() >= self::FREE_SHIPPING_MIN_PROD_NB
        ) {
            return 1;
        } else {
            return 0;
        }
    }

    protected function countAllProducts(): int
    {
        return (int)array_sum(array_column($this->products, 'product_quantity'));
    }
}
