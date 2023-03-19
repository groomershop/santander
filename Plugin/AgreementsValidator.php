<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Plugin;

use Aurora\Santander\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CheckoutAgreements\Model\AgreementsValidator as MagentoAgreementsValidator;

class AgreementsValidator
{
    public const SANTANDER_AGREEMENT_ID = 'santander_configuration/agreement_configuration/santander_agreement';

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param Data $dataHelper
     */
    public function __construct(Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Add santander agreement
     *
     * @param MagentoAgreementsValidator $subject
     * @param int[] $agreementIds
     *
     * @throws NoSuchEntityException
     * @return array
     */
    public function beforeIsValid(MagentoAgreementsValidator $subject, $agreementIds = [])
    {
        $santanderId = $this->dataHelper->getConfigValue(self::SANTANDER_AGREEMENT_ID);

        if (!in_array($santanderId, $agreementIds)) {
            $agreementIds[] = $santanderId;
        }

        return [$agreementIds];
    }
}
