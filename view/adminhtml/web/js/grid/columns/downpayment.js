// /**
//  * @copyright Copyright (c) 2025 Aurora Creation Sp. z o.o. (http://auroracreation.com)
//  */

define([
    'jquery',
    'Magento_Ui/js/grid/columns/column',
    'Magento_Catalog/js/price-utils'
], function ($, Column, priceUtils) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },
        getLabel: function (record) {
            var label = this._super(record);

            if (parseFloat(record.santander_bank_downpayment) === 0) {
               return '<span>0</span>';
            }

            var price = priceUtils.formatPrice(
                record.santander_bank_downpayment,
                {
                    pattern: '%s zł',
                }
            );
            label = '<span class="downpayment-content"><span>' +
                $.mage.__('Own payment (to be collected from the customer before the goods are released): ') +
                '</span><span class="downpayment-price">' + price + '</span></span>';

            return label;
        }
    });
});

