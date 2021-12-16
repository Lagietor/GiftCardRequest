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

interface DataCollectorInterface
{
    public const PRECISION = 2;
    public const PAYMENT_DEFAULT = 'payu';
    public const STATUS_EMAIL_CHANGE = 0;
    public const PRODUCT_UNIT_FP = 0;

    public const SHIPPING_DEPEND_ON_WEIGHT = 1;
    public const SHIPPING_DEPEND_ON_AMOUNT = 2;
    public const SHIPPING_DEPEND_ON_DEFAULT = 0;
    public const FREE_SHIPPING_MIN_PROD_NB = 4;

    public function getData(): array;
    public function getName(): string;
}
