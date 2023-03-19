<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Test\Unit\ViewModel;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote\Payment;
use Magento\Framework\App\RequestInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Aurora\Santander\ViewModel\ApplicationStatus;

class ApplicationStatusTest extends TestCase
{
    /**
     * @var MockObject|Session
     */
    public MockObject|Session $checkoutSession;

    /**
     * @var MockObject|RequestInterface
     */
    public MockObject|RequestInterface $request;

    /**
     * @var MockObject|BlockRepositoryInterface
     */
    public MockObject|BlockRepositoryInterface $blockRepository;

    /**
     * @var MockObject|ApplicationStatus
     */
    public MockObject|ApplicationStatus $viewModel;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->blockRepository = $this->getMockBuilder(BlockRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewModel = $this->getMockBuilder(ApplicationStatus::class)
            ->setConstructorArgs(
                [
                    $this->checkoutSession,
                    $this->request,
                    $this->blockRepository,
                ]
            )
            ->onlyMethods(['hasValidParams', 'isSantanderPayment', 'getLastOrder'])
            ->getMock();

        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order = $this->getMockBuilder(Order::class)
            ->onlyMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $order->expects($this->any())
            ->method('getPayment')
            ->willReturn($payment);

        $this->checkoutSession->expects($this->any())
            ->method('getLastRealOrder')
            ->willReturn($order);
    }

    public function testGetApplicationId()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('id_wniosku');

        $this->viewModel->getApplicationId();
    }
}
