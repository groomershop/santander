<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\Test\Unit\ViewModel;

class RedirectTest extends \PHPUnit\Framework\TestCase
{
    public $controller;
    public $context;
    public $pageFactory;

    public function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new \Aurora\Santander\Controller\Eraty\Redirect(
            $this->context,
            $this->pageFactory
        );
    }

    public function testExecute()
    {
        $this->pageFactory->expects($this->once())
            ->method('create');

        $this->controller->execute();
    }
}
