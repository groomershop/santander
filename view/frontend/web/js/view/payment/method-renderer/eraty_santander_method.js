define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aurora_Santander/payment/eraty_santander'
            },

            redirectAfterPlaceOrder: false,

            afterPlaceOrder: function () {
                console.log(window.checkoutConfig.santander.redirect);

                window.location.replace(
                    window.checkoutConfig.santander.redirect
                );
            },
        });
    }
);
