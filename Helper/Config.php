<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Helper;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Config
{
    public const API_LOGIN = 'santander_configuration/auto_update/api_login';

    public const API_PASSWD = 'santander_configuration/auto_update/api_password';

    public const API_SHOP_NUMBER = 'santander_configuration/auto_update/shop_id';

    public const API_CERT = 'santander_configuration/auto_update/cert';

    public const API_CERT_PASSWD = 'santander_configuration/auto_update/cert_password';

    public const MODULE_PREFIX = 'santander_configuration/logger/module_prefix';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DirectoryList $directoryList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DirectoryList $directoryList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->scopeConfig->getValue(self::API_LOGIN);
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->scopeConfig->getValue(self::API_PASSWD);
    }

    /**
     * Get shop number
     *
     * @return string
     */
    public function getShopNumber()
    {
        return $this->scopeConfig->getValue(self::API_SHOP_NUMBER);
    }

    /**
     * Get cert path
     *
     * @throws FileSystemException
     * @return string
     */
    public function getCertPath()
    {
        $cert = $this->scopeConfig->getValue(self::API_CERT);

        return sprintf('%s/%s', $this->directoryList->getPath('var'), $cert);
    }

    /**
     * Get cert password
     *
     * @return string
     */
    public function getCertPasswd()
    {
        return $this->scopeConfig->getValue(self::API_CERT_PASSWD);
    }

    /**
     * Get prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->scopeConfig->getValue(self::MODULE_PREFIX);
    }
}
