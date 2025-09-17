<?php
/**
 * @copyright Copyright (c) 2025 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\Model;

use Aurora\Santander\Model\Form;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Exception;

// phpcs:disable Generic.Files.LineLength.TooLong

class InitRequest
{
    public const FORM_URL = 'https://wniosek.eraty.pl/formularz/';

    /**
     * @param \Aurora\Santander\Model\Form $form
     * @param CurlFactory $curlFactory
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected Form $form,
        protected CurlFactory $curlFactory,
        protected Json $json,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * Execute the init form request
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $data = $this->form->getData();

            $curl = $this->curlFactory->create();
            $curl->addHeader('Access-Control-Allow-Origin', '*');
            $curl->addHeader('Access-Control-Allow-Methods', 'POST');
            $curl->addHeader('Access-Control-Allow-Headers', 'Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');
            $curl->post(self::FORM_URL, $data);
        } catch (Exception $e) {
            $this->logger->error('Aurora_Santander', [$e->getMessage()]);
        }
    }
}
