/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'GOS_WholesalePayment/payment/wholesale'
            }
        });
    }
);
