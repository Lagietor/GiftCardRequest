CREATE TABLE ordercreatedata (
    order_id int PRIMARY KEY AUTO_INCREMENT NOT NULL,
    user_id int NOT NULL,
    date date NOT NULL,
    status_date date NOT NULL,
    confirm_date date NOT NULL,
    delivery_date date NOT NULL,
    status_id int NOT NULL,
    sum double NOT NULL,
    payment_id int NOT NULL,
    user_order boolean NOT NULL,
    shipping_id int NOT NULL,
    shipping_cost double NOT NULL,
    email text NOT NULL,
    delivery_code text NOT NULL,
    code text NOT NULL,
    confirm boolean NOT NULL,
    notes text NOT NULL,
    notes_priv text NOT NULL,
    notes_pub text NOT NULL,
    currency_name text NOT NULL,
    currency_rate double NOT NULL,
    paid double NOT NULL,
    ip_address text NOT NULL,
    discount_client float NOT NULL,
    discount_group float NOT NULL,
    discount_levels float NOT NULL,
    discount_code float NOT NULL,
    shipping_vat int NOT NULL,
    shipping_vat_value double NOT NULL,
    shipping_vat_name text NOT NULL,
    code_id int,
    lang_id int,
    origin int NOT NULL,
    promo_code text NOT NULL,
    billingAddress object NOT NULL,
    billingAddress.address_id int NOT NULL,
    billingAddress.order_id int NOT NULL,
    billingAddress.type int NOT NULL,
    billingAddress.firstname text NOT NULL,
    billingAddress.lastname text NOT NULL,
    billingAddress.company text NOT NULL,
    billingAddress.tax_id text NOT NULL,
    billingAddress.pesel text NOT NULL,
    billingAddress.postcode text NOT NULL,
    billingAddress.street1 text NOT NULL,
    billingAddress.street2 text NOT NULL,
    billingAddress.state text NOT NULL,
    billingAddress.country text NOT NULL,
    billingAddress.phone text NOT NULL,
    billingAddress.country_code text NOT NULL,
    deliveryAddress object NOT NULL,
    deliveryAddress.address_id int NOT NULL,
    deliveryAddress.order_id int NOT NULL,
    deliveryAddress.type int NOT NULL,
    deliveryAddress.firstname text NOT NULL,
    deliveryAddress.lastname text NOT NULL,
    deliveryAddress.company text NOT NULL,
    deliveryAddress.tax_id text NOT NULL,
    deliveryAddress.pesel text NOT NULL,
    deliveryAddress.city text NOT NULL,
    deliveryAddress.postcode text NOT NULL,
    deliveryAddress.street1 text NOT NULL,
    deliveryAddress.street2 text NOT NULL,
    deliveryAddress.state text NOT NULL,
    deliveryAddress.country text NOT NULL,
    deliveryAddress.phone text NOT NULL,
    deliveryAddress.country_code text NOT NULL,
    auction array NOT NULL,
    auction.auction_order_id int NOT NULL,
    auction.auction_id int NOT NULL,
    auction.real_auction_id int NOT NULL,
    auction.order_id int NOT NULL,
    auction.buyer_id int NOT NULL,
    auction.status_time text NOT NULL,
    auction.payment_time text NOT NULL,
    auction.payment_method text NOT NULL,
    auction.shipment_method text NOT NULL,
    auction.transaction_id int NOT NULL,
    auction.buyer_login text NOT NULL,
    shipping object NOT NULL,
    shipping.shipp_id int NOT NULL,
    shipping.name text NOT NULL,
    shipping.description text NOT NULL,
    shipping.cost double NOT NULL,
    shipping.depend_on_w int NOT NULL,
    shipping.zone_id int NOT NULL,
    shipping.max_weight float NOT NULL,
    shipping.min_weight float NOT NULL,
    shipping.free_shipping float NOT NULL,
    shipping.order int NOT NULL,
    shipping.is_default boolean NOT NULL,
    shipping.pkwiu text NOT NULL,
    shipping.mobile boolean NOT NULL,
    shipping.engine text NOT NULL,
    shipping.callback_url text NOT NULL,
    status object NOT NULL,
    status.status_id int NOT NULL,
    status.default boolean NOT NULL,
    status.color text NOT NULL,
    status.type int NOT NULL,
    status.email_change boolean NOT NULL,
    status.order int NOT NULL,
    status.name text NOT NULL,
    message text NOT NULL,
    payment object NOT NULL,
    payment.payment_id int NOT NULL,
    payment.order int NOT NULL,
    payment.name text NOT NULL,
    payment.title text NOT NULL,
    payment.description text NOT NULL,
    payment.notify_mail text NOT NULL,
    products array NOT NULL,
    product[n].id int NOT NULL,
    product[n].order_id int NOT NULL,
    product[n].product_id int NOT NULL,
    product[n].stock_id int NOT NULL,
    product[n].price double NOT NULL,
    product[n].discount_perc double NOT NULL,
    product[n].quantity float NOT NULL,
    product[n].delivery_time float NOT NULL,
    product[n].name text NOT NULL,
    product[n].code text NOT NULL,
    product[n].pkwiu text NOT NULL,
    product[n].tax text NOT NULL,
    product[n].tax_value int NOT NULL,
    product[n].unit text NOT NULL,
    product[n].option text NOT NULL,
    product[n].unit_fp boolean NOT NULL,
    product[n].weight double NOT NULL,
    additional_fields array NOT NULL,
    additional_fields[n].field_id int NOT NULL,
    additional_fields[n].type int NOT NULL,
    additional_fields[n].locate int NOT NULL,
    additional_fields[n].req boolean NOT NULL,
    additional_fields[n].active boolean NOT NULL,
    additional_fields[n].order int NOT NULL,
    additional_fields[n].value text NOT NULL   )ng