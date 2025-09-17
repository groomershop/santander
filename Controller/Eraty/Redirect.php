<?php
/**
 * @copyright Copyright (c) 2025 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\Controller\Eraty;

use Aurora\Santander\Model\InitRequest;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Redirect implements HttpGetActionInterface
{
    /**
     * @param PageFactory $pageFactory
     * @param InitRequest $initRequest
     */
    public function __construct(
        public PageFactory $pageFactory,
        protected InitRequest $initRequest
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->initRequest->execute();

        return $this->pageFactory->create();
    }
}
