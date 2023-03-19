<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Model\Payment;

use Aurora\Santander\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class SantanderConfigProvider implements ConfigProviderInterface
{
    public const SANTANDER_AGREEMENT_ID = 'santander_configuration/agreement_configuration/santander_agreement';

    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @param UrlInterface $urlBuilder
     * @param Data $dataHelper
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Data $dataHelper
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Get redirect URL
     *
     * @throws NoSuchEntityException
     * @return array
     */
    public function getConfig()
    {
        $config['santander']['redirect'] = $this->urlBuilder->getUrl('santander/eraty/redirect');
        $config['santander']['agreement_id'] = $this->dataHelper->getConfigValue(self::SANTANDER_AGREEMENT_ID);

        return $config;
    }
}
