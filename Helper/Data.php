<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (https://auroracreation.com)
 */
namespace Aurora\Santander\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $configPath
     * @return string
     */
    public function getConfigValue(string $configPath)
    {
        $store = $this->storeManager->getStore()->getId();
        $storeScope = ScopeInterface::SCOPE_STORE;
        $configValue = $this->scopeConfig->getValue($configPath, $storeScope, $store);

        return $configValue;
    }
}
