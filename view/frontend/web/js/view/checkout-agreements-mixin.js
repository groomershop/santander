/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_CheckoutAgreements/js/model/agreements-modal'
], function (ko, $, Component, agreementsModal) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        agreementManualMode = 1,
        agreementsConfig = checkoutConfig ? checkoutConfig.checkoutAgreements : {};

    var mixin = {
        defaults: {
          template: 'Aurora_Santander/checkout/checkout-agreements'
        },
        isVisible: agreementsConfig.isEnabled,
        agreements: agreementsConfig.agreements,
        modalTitle: ko.observable(null),
        modalContent: ko.observable(null),
        contentHeight: ko.observable(null),
        modalWindow: null,
        /**
         * Checks if agreement is santander
         *
         * @param {Object} context - the ko context
         * @param {Object} element
         */
        isSantander: function (context, element) {
            var checkboxId = this.getCheckboxId(context, element.agreementId)
            var santanderAgreementId = window.checkoutConfig.santander.agreement_id;
            if (checkboxId == 'agreement_eraty_santander_' + santanderAgreementId) {
                return true;
            }
            return false;
        },
        /**
         * Checks if agreement is santander
         *
         * @param {Object} context - the ko context
         * @param {Object} element
         */
        santanderAgreement: function (element) {
            var santanderAgreementId = window.checkoutConfig.santander.agreement_id;
            if (element.agreementId == santanderAgreementId) {
                return true;
            }
            return false;
        },

        getContent: function (element) {
            var content = element.content;
            return content.replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"");
        }
    }
    return function(target) {
        return target.extend(mixin)
    }
});
