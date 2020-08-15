<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Test\Unit\Model\Payment;

class SantanderConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    public $urlBuilder;
    public $model;

    public function setUp()
    {
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataHelper = $this->getMockBuilder(\Aurora\Santander\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Aurora\Santander\Model\Payment\SantanderConfigProvider(
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
        $this->assertTrue(is_array($result) && isset($result['santander']['redirect']) );
        $this->assertTrue(is_array($result) && isset($result['santander']['agreement_id']) );
    }
}
