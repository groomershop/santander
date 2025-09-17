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
     * @var MockObject|Data
     */
    public MockObject|Data $dataHelper;

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

        $result = $this->model->getConfig();

        $this->assertTrue(is_array($result) && isset($result['santander']['redirect']));
    }
}
