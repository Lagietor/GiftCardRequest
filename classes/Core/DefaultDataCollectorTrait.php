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

namespace Gcr\Core;

use \Module;
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
use \DateTime;
use \DateInterval;
use \Validate;
use \CartRule;
use \OrderCarrier;
use \Cart;
use stdClass;

trait DefaultDataCollectorTrait
{
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

    /** @var int */
    protected $idCart;

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

    public function getData(): stdClass
    {
        $this->readProductsFromOrder();
        $this->customer = new Customer($this->order->id_customer);
        if (! Validate::isLoadedObject($this->customer)) {
            throw new \Exception('Could not load Customer - ID: ' . $this->order->id_customer);
        }
        $this->orderStatusInfo = $this->getOrderStatusInfo();

        $this->carrier = new Carrier($this->order->id_carrier);
        if (! Validate::isLoadedObject($this->carrier)) {
            throw new \Exception('Could not load Carrier - ID: ' . $this->order->id_carrier);
        }
        $this->taxRuleGroup = new TaxRulesGroup(
            $this->carrier->getIdTaxRulesGroupByIdCarrier($this->order->id_carrier)
        );
        if (! Validate::isLoadedObject($this->taxRuleGroup)) {
            throw new \Exception('Could not load TaxRuleGroup - ID Carrierer: ' . $this->order->id_carrier);
        }

        $this->billingAddress = new Address($this->order->id_address_invoice);
        if (! Validate::isLoadedObject($this->billingAddress)) {
            throw new \Exception('Could not load billing Address - ID: ' . $this->order->id_address_invoice);
        }
        $this->billingCountry = new Country($this->billingAddress->id);
        if (! Validate::isLoadedObject($this->billingCountry)) {
            throw new \Exception('Could not load billing Country - ID: ' . $this->billingAddress->id);
        }
        $this->billingState = $this->billingAddress->id_state
            ? new State((int) $this->billingAddress->id_state)
            : false;
        if ($this->billingState) {
            if (! Validate::isLoadedObject($this->billingState)) {
                throw new \Exception('Could not load billing Country - ID: ' . $this->billingAddress->id_state);
            }
        }

        $this->deliveryAddress = new Address($this->order->id_address_delivery);
        if (! Validate::isLoadedObject($this->deliveryAddress)) {
            throw new \Exception('Could not load delivery Address - ID: ' . $this->order->id_address_delivery);
        }
        $this->deliveryCountry = new Country($this->deliveryAddress->id);
        if (! Validate::isLoadedObject($this->deliveryCountry)) {
            throw new \Exception('Could not load delivery Country - ID: ' . $this->deliveryAddress->id);
        }
        $this->deliveryState = $this->deliveryAddress->id_state
            ? new State((int) $this->deliveryAddress->id_state)
            : false;
        if ($this->deliveryState) {
            if (! Validate::isLoadedObject($this->deliveryState)) {
                throw new \Exception('Could not load delivery Country - ID: ' . $this->deliveryAddress->id_state);
            }
        }

        $this->state = new OrderState($this->order->current_state);
        if (! Validate::isLoadedObject($this->state)) {
            throw new \Exception('Could not load OrdeState - ID: ' . $this->order->current_state);
        }
        $this->payment = Module::getInstanceById(Module::getModuleIdByName($this->order->module));
        if (! Validate::isLoadedObject($this->payment)) {
            throw new \Exception('Could not load payment Module - Name: ' . $this->order->module);
        }

        $this->idCart = Cart::getCartIdByOrderId($this->order->id);
        if (empty($this->idCart)) {
            throw new \Exception('Could not load Cart - ID order: ' . $this->order->id);
        }

        $data = new stdClass();
        $data->order_id = $this->order->reference;
        $data->user_id = $this->customer->id;
        $data->date = $this->order->date_add;
        $data->status_date = $this->orderStatusInfo['date_add'];
        $data->confirm_date = $this->order->date_add;
        $data->delivery_date = $this->getDeliveryDate();
        $data->status_id = $this->orderStatusInfo['id_order_state'];
        $data->sum = round($this->order->total_paid_tax_incl, self::PRECISION);
        $data->payment_id = Module::getModuleIdByName($this->order->module);
        $data->user_order = 1; // only register users
        $data->shipping_id = $this->order->id_carrier;
        $data->shipping_cost = round($this->order->total_shipping_tax_incl, self::PRECISION);
        $data->email = $this->customer->email;
        $data->delivery_code = '';
        $data->code = '';
        $data->confirm = 0;
        $data->notes = $this->order->getFirstMessage();
        $data->notes_priv = '';
        $data->notes_pub = '';
        $data->currency_id = $this->order->id_currency;
        $data->currency_name = 'PLN'; // only PLN supported
        $data->currency_rate = 1;
        $data->paid = 0; // must be set in data collector
        $data->ip_address = ''; // must be set in data collector

        $data->discount_client = $this->getDiscountClient();
        $data->discount_group = $this->getPercentDiscountGroup();
        $data->discount_levels = 0;
        $data->discount_code = 0;  // will be set later
        $data->shipping_vat = $this->carrier->getIdTaxRulesGroupByIdCarrier($this->order->id_carrier);
        $data->shipping_vat_value = round(
            $this->order->total_shipping_tax_incl - $this->order->total_shipping_tax_excl,
            self::PRECISION
        );
        $data->shipping_vat_name = $this->taxRuleGroup->name;
        $data->code_id = $this->getCodeId();
        $data->lang_id = $this->order->id_lang;
        $data->origin = 1;
        $data->promo_code = $this->getPromoCode();

        $billingAddress = new stdClass();
        $billingAddress->address_id = $this->billingAddress->id;
        $billingAddress->order_id = $this->order->reference;
        $billingAddress->type = 1;
        $billingAddress->firstname = $this->billingAddress->firstname;
        $billingAddress->lastname = $this->billingAddress->lastname;
        $billingAddress->company = $this->billingAddress->company;
        $billingAddress->tax_id = $this->billingAddress->vat_number;
        $billingAddress->pesel = isset($this->billingAddress->pesel) ? $this->billingAddress->pesel : '';
        $billingAddress->city = $this->billingAddress->city;
        $billingAddress->postcode = $this->billingAddress->postcode;
        $billingAddress->street1 = $this->billingAddress->address1;
        $billingAddress->street2 = $this->billingAddress->address2;
        $billingAddress->state = $this->billingAddress->id_state ? $this->billingState->name : '';
        $billingAddress->country = $this->billingAddress->country;
        $billingAddress->phone = $this->billingAddress->phone_mobile
            ? $this->billingAddress->phone_mobile
            : $this->billingAddress->phone;
        $billingAddress->country_code = $this->billingCountry->iso_code;

        $data->billingAddress = $billingAddress;

        $deliveryAddress = new stdClass();
        $deliveryAddress->address_id = $this->deliveryAddress->id;
        $deliveryAddress->order_id = $this->order->reference;
        $deliveryAddress->type = 2;
        $deliveryAddress->firstname = $this->deliveryAddress->firstname;
        $deliveryAddress->lastname = $this->deliveryAddress->lastname;
        $deliveryAddress->company = $this->deliveryAddress->company;
        $deliveryAddress->tax_id = $this->deliveryAddress->vat_number;
        $deliveryAddress->pesel = isset($this->deliveryAddress->pesel) ? $this->deliveryAddress->pesel : '';
        $deliveryAddress->city = $this->deliveryAddress->city;
        $deliveryAddress->postcode = $this->deliveryAddress->postcode;
        $deliveryAddress->street1 = $this->deliveryAddress->address1;
        $deliveryAddress->street2 = $this->deliveryAddress->address2;
        $deliveryAddress->state = $this->deliveryAddress->id_state ? $this->deliveryState->name : '';
        $deliveryAddress->country = $this->deliveryAddress->country;
        $deliveryAddress->phone = $this->deliveryAddress->phone_mobile
            ? $this->deliveryAddress->phone_mobile
            : $this->deliveryAddress->phone;
        $deliveryAddress->country_code = $this->deliveryCountry->iso_code;

        $data->deliveryAddress = $deliveryAddress;

        $shipping = new stdClass();
        $shipping->shipping_id = $this->carrier->id;
        $shipping->name = $this->carrier->name;
        $shipping->description = '';
        $shipping->cost = round($this->order->total_shipping_tax_incl, self::PRECISION);
        $shipping->depend_on_w = $this->getShippingDependOn();
        $shipping->zone_id = $this->deliveryCountry->id_zone;
        $shipping->tax_id = $this->carrier->getIdTaxRulesGroupByIdCarrier($this->order->id_carrier);
        $shipping->max_weight = (float)$this->carrier->max_weight;
        $shipping->min_weight = 0.0;
        $shipping->free_shipping = $this->isFreeShipping();
        $shipping->order = $this->carrier->position;
        $shipping->is_default = (int)($this->carrier->id == Configuration::get('PS_CARRIER_DEFAULT'));
        $shipping->pkwiu = '';
        $shipping->mobile = 1;
        $shipping->engine = '';
        $shipping->callback_url = $this->getCallbackUrl();

        $data->shipping = $shipping;

        $status = new stdClass();
        $status->status_id = $this->order->current_state;
        $status->default = (int)($this->payment->name == self::PAYMENT_DEFAULT);
        $status->color = $this->state->color;
        $status->type = self::STATUS_TYPE;
        $status->email_change = self::STATUS_EMAIL_CHANGE;
        $status->order = $this->order->current_state;
        $status->name = $this->state->name[$this->defaultIdLang];

        $data->status = $status;

        $data->message = $this->getMessage($this->orderStatusInfo['id_order_state'], $this->defaultIdLang);

        $payment = new stdClass();
        $payment->payment_id = $this->payment->id;
        $payment->order = $this->getPaymentOrder();
        $payment->name = $this->payment->name;
        $payment->title = $this->payment->displayName;
        $payment->description = $this->payment->description;
        $payment->notify_mail = '';
        // GiftCard info
        $payment->giftcard = new stdClass();
        $payment->giftcard->title = 'GiftCard';
        $payment->giftcard->description = 'Karta podarunkowa';
        $payment->giftcard->value = $this->getGiftCardTotal();
        $payment->giftcard->card_numbers = $this->getGiftCardInfo();

        $data->payment = $payment;

        // products info
        $data->products = $this->getProducts($this->order);

        // set 'discount_code' value
        if (! empty($this->products)) {
            $firstProduct = reset($this->products);
            $data->discount_code = floor($data->products[(int)$firstProduct['id_product']]->discount_perc);
        }

        $data->additional_fields = [];

        return $data;
    }

    protected function getGiftCardTotal(): float
    {
        if (! class_exists('CartGiftcard') || ! class_exists('GiftcardModel')) {
            return 0.0;
        }

        return round(\CartGiftcard::getTotalDiscounts($this->idCart), self::PRECISION);
    }

    protected function getGiftCardInfo(): array
    {
        $gcInfo = [];

        if (! class_exists('CartGiftcard') || ! class_exists('GiftcardModel')) {
            return $gcInfo;
        }

        $cartGiftCards = \CartGiftcard::getByIdCart($this->idCart);
        if (empty($cartGiftCards)) {
            return $gcInfo;
        }

        foreach ($cartGiftCards as $singleCart) {
            $tmpGiftCard = new \GiftcardModel($singleCart->id_giftcard);
            if (! Validate::isLoadedObject($tmpGiftCard)) {
                continue;
            }

            $tmpGcInfo = new stdClass();
            $tmpGcInfo->nr = $tmpGiftCard->card_number;
            $tmpGcInfo->value = round($singleCart->balance, self::PRECISION);

            $gcInfo[] = $tmpGcInfo;
        }

        return $gcInfo;
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

    protected function getProducts(Order $order): array
    {
        $allProducts = [];
        $inOrder = $this->products;

        foreach ($inOrder as $p) {
            $idProduct = (int)$p['id_product'];
            $product = $this->getRealProduct($idProduct);
            $IdProductAttr = isset($p['product_attribute_id'])
                ? (int)$p['product_attribute_id']
                : 0;

            $prod = new stdClass();
            $prod->id = $idProduct;
            $prod->order_id = $order->reference;
            $prod->product_id = $p['reference'];
            $prod->stock_id = $IdProductAttr;
            $prod->price = $p['product_price_wt'];
            $prod->discount_perc = $this->getProductPercentDiscount($idProduct);
            $prod->quantity = $p['product_quantity'];
            $prod->delivery_time = $this->getDeliveryTime($idProduct);
            $prod->name = $p['product_name'];
            $prod->code = $product->reference;
            $prod->pkwiu = '';
            $prod->tax = $p['tax_name'];
            $prod->tax_value = round($p['product_price_wt'] - $p['product_price'], self::PRECISION);
            $prod->unit = (string)($product->unity);
            $prod->option = $this->getProductOption($IdProductAttr);
            $prod->unit_fp = self::PRODUCT_UNIT_FP;
            $prod->weight = round($p['weight'], self::PRECISION);

            $allProducts[$idProduct] = $prod;
        }

        return $allProducts;
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

    protected function getCallbackUrl(): string
    {
        $orderCarrierer = new OrderCarrier($this->order->getIdOrderCarrier());
        if (! Validate::isLoadedObject($orderCarrierer)) {
            return '';
        }

        return str_replace('@', $orderCarrierer->tracking_number, $this->carrier->url);
    }

    protected function getPaymentOrder(): int
    {
        $sql = 'SELECT `position`
            FROM `' . _DB_PREFIX_ . 'hook_module`
            WHERE `id_hook` = (
                SELECT `id_hook`
                FROM `' . _DB_PREFIX_ . 'hook`
                WHERE `name` = "paymentOptions")
            AND id_module = ' . $this->payment->id;

        return (int)(Db::getInstance()->getValue($sql));
    }

    protected function getProductOption(int $idAttr): string
    {
        $sql = 'SELECT `name`
            FROM `' . _DB_PREFIX_ . 'attribute_lang`
            WHERE `id_attribute` = (
                SELECT `id_attribute`
                FROM `' . _DB_PREFIX_ . 'product_attribute_combination`
                WHERE `id_product_attribute` = ' . $idAttr . '
                LIMIT 1
            )';

        return (string)(Db::getInstance()->getValue($sql));
    }
}
