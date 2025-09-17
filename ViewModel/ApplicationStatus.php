<?php
/**
 * @copyright Copyright (c) 2024 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order as Order;
use Magento\Framework\App\RequestInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ApplicationStatus implements ArgumentInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var Order
     */
    public $order;

    /**
     * @param Session $checkoutSession
     * @param RequestInterface $request
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        Session $checkoutSession,
        RequestInterface $request,
        BlockRepositoryInterface $blockRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->blockRepository = $blockRepository;
    }

    /**
     * Get last order id from session
     *
     * @return void
     */
    private function getLastOrder()
    {
        $this->order = $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Check is Santander method selected
     *
     * @return boolean
     */
    private function isSantanderPayment()
    {
        $this->getLastOrder();
        if ($this->order->getPayment()->getMethod() == 'eraty_santander') {
            return true;
        }

        return false;
    }

    /**
     * Get Santander CMS block
     *
     * @return string
     */
    public function getCmsBlock()
    {
        if ($this->isSantanderPayment() && $this->hasValidParams()) {
            try {
                $blockId = ($this->request->getParam('result') == 1) ? 'eraty_success' : 'eraty_failure';
                $block = $this->blockRepository->getById($blockId);
            } catch (LocalizedException $exception) {
                return '';
            }

            return $block->getContent();
        }

        return '';
    }

    /**
     * Check if request has required parameters
     *
     * @return boolean
     */
    private function hasValidParams()
    {
        if ($this->request->getParam('result') !== null && $this->request->getParam('order') == $this->order->getId()) {
            return true;
        }

        return false;
    }

    /**
     * Get application ID from request
     *
     * @return int|null
     */
    public function getApplicationId()
    {
        return $this->request->getParam('id_wniosku');
    }
}
