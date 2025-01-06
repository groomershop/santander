/*
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 *
 */
var config = {
    'config': {
        mixins: {
            'Magento_CheckoutAgreements/js/view/checkout-agreements': {
                'Aurora_Santander/js/view/checkout-agreements-mixin': true
            },
            'Magento_Catalog/js/price-box': {
                'Aurora_Santander/js/pricebox': true
            }
        }
    }
}