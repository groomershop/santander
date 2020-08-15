define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'eraty_santander',
                component: 'Aurora_Santander/js/view/payment/method-renderer/eraty_santander_method'
            }
        );
        return Component.extend({});
    }
);
