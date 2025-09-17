<?php

/**
 * @copyright Copyright (c) 2022 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */

declare(strict_types=1);

namespace Aurora\Santander\Test\Unit\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NotFoundException;
use Aurora\Santander\Controller\Eraty\Redirect;
use Aurora\Santander\Model\InitRequest;

class RedirectTest extends TestCase
{
    /**
     * @var Redirect
     */
    public Redirect $controller;

    /**
     * @var MockObject|Context
     */
    public MockObject|Context $context;

    /**
     * @var MockObject|PageFactory
     */
    public MockObject|PageFactory $pageFactory;

    /**
     * @var MockObject|InitRequest
     */
    public MockObject|InitRequest $initRequest;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->context = $this
            ->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageFactory = $this
            ->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->initRequest = $this
            ->getMockBuilder(InitRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new Redirect($this->pageFactory, $this->initRequest);
    }

    public function testExecute()
    {
        $this->pageFactory->expects($this->once())
            ->method('create');

        try {
            $this->controller->execute();
        } catch (NotFoundException) {
        }
    }
}
