<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Aurora\Santander\Plugin;

/**
 * Class AgreementsValidator
 */
class AgreementsValidator
{
    const SANTANDER_AGREEMENT_ID = 'santander_configuration/agreement_configuration/santander_agreement';

    /**
     * @var \Aurora\Santander\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param \Aurora\Santander\Helper\Data $dataHelper
     */
    public function __construct(        
        \Aurora\Santander\Helper\Data $dataHelper
    )
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Add santander agreement
     *
     * @param int[] $agreementIds
     * @return array
     */
    public function beforeIsValid(\Magento\CheckoutAgreements\Model\AgreementsValidator $subject, $agreementIds = [])
    {
        $santanderId = $this->dataHelper->getConfigValue(self::SANTANDER_AGREEMENT_ID);
        if (!in_array($santanderId, $agreementIds)) {
            $agreementIds[] = $santanderId; 
        }
        return [$agreementIds];
    }
}
