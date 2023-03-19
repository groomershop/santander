<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Test\Unit\Model\Payment;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\UrlInterface;
use Aurora\Santander\Helper\Data;
use Aurora\Santander\Model\Payment\SantanderConfigProvider;

class SantanderConfigProviderTest extends TestCase
{
    /**
     * @var MockObject|UrlInterface
     */
    public MockObject|UrlInterface $urlBuilder;

    /**
     * @var MockObject|SantanderConfigProvider
     */
    public MockObject|SantanderConfigProvider $model;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->urlBuilder = $this
            ->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataHelper = $this
            ->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SantanderConfigProvider(
            $this->urlBuilder,
            $this->dataHelper
        );
    }

    public function testGetConfig()
    {
        $url = 'redirect_url';

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);

        $this->dataHelper->expects($this->once())
            ->method('getConfigValue')
            ->willReturn(1);

        $result = $this->model->getConfig();

        $this->assertTrue(is_array($result) && isset($result['santander']['redirect']));
        $this->assertTrue(is_array($result) && isset($result['santander']['agreement_id']));
    }
}
