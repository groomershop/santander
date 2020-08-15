<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Model\Payment;

/**
 * Class SantanderConfigProvider
 */
class SantanderConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const SANTANDER_AGREEMENT_ID = 'santander_configuration/agreement_configuration/santander_agreement';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    public $urlBuilder;

    /**
     * @var \Aurora\Santander\Helper\Data 
     */
    public $dataHelper;

    /**
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Aurora\Santander\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Aurora\Santander\Helper\Data $dataHelper
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Get redirect URL
     * @return string
     */
    public function getConfig()
    {
        $config['santander']['redirect'] = $this->urlBuilder->getUrl('santander/eraty/redirect');
        $config['santander']['agreement_id'] =  $this->dataHelper->getConfigValue(self::SANTANDER_AGREEMENT_ID);
        return $config;
    }
}
