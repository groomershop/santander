<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Test\Unit\ViewModel;

class ApplicationStatusTest extends \PHPUnit\Framework\TestCase
{
    public $checkoutSession;
    public $request;
    public $blockRepository;
    public $viewModel;

    public function setUp()
    {
        $this->checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->blockRepository = $this->getMockBuilder(\Magento\Cms\Api\BlockRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewModel = $this->getMockBuilder(\Aurora\Santander\ViewModel\ApplicationStatus::class)
            ->setConstructorArgs(
                [
                    $this->checkoutSession,
                    $this->request,
                    $this->blockRepository
                ]
            )
            ->setMethods(['hasValidParams', 'isSantanderPayment','getLastOrder'])
            ->getMock();

        $payment = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['getPayment'])
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
