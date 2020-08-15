<?php
/**
 * @copyright Copyright (c) 2020 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
namespace Aurora\Santander\ViewModel;

/**
 * ApplicationStatus
 */
class ApplicationStatus implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var \Magento\Sales\Model\Order
     */
    public $order;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->blockRepository = $blockRepository;
    }

    /**
     * Get last order id from session
     * @return integer
     */
    private function getLastOrder()
    {
        $this->order = $this->checkoutSession->getLastRealOrder();
    }
    /**
     * Check is Santander method selected
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
     * @return string
     */
    public function getCmsBlock()
    {
        if ($this->isSantanderPayment() && $this->hasValidParams()) {
            try {
                $blockId = ($this->request->getParam('result') == 1) ? 'eraty_success' : 'eraty_failure' ;
                $block = $this->blockRepository->getById($blockId);
            } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                return '';
            }

            return $block->getContent();
        }

        return '';
    }

    /**
     * Check if request has required parameters
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
     * @return int|null
     */
    public function getApplicationId()
    {
        return $this->request->getParam('id_wniosku');
    }
}
