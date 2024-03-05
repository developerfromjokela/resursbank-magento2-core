<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Controller\Frontend;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Api\LogInterface;
use Resursbank\Core\Model\PaymentMethodRepository;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Api;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReadMore implements HttpGetActionInterface
{
    /**
     * @param LogInterface $log
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param PaymentMethodRepository $methodRepository
     * @param Api $api
     * @param Credentials $credentials
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        protected LogInterface $log,
        protected RequestInterface $request,
        protected readonly ResultFactory $resultFactory,
        protected readonly PaymentMethodRepository $methodRepository,
        protected readonly Api $api,
        protected readonly Credentials $credentials,
        protected readonly StoreManagerInterface $storeManager,
    ) {
    }

    /**
     * Execute controller
     *
     * @throws Exception
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Json $result */
        $result = $this->resultFactory->create(type: ResultFactory::TYPE_JSON);

        try {
            $result->setData(data: ['html' => $this->getHtml()]);
        } catch (Exception $e) {
            $this->log->exception(error: $e);

            $result->setData(data: [
                'message' => [
                    'error' => __(
                        'Something went wrong when fetching the content. ' .
                        'Please try again.'
                    )
                ]
            ]);
        }

        return $result;
    }

    /**
     * Get output HTML
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws ValidatorException
     * @throws Exception
     */
    private function getHtml(): string
    {
        $store = $this->storeManager->getStore();
        $credentials = $this->credentials->resolveFromConfig(
            scopeCode: $store->getCode(),
            scopeType: ScopeInterface::SCOPE_STORES
        );

        return $this->api->getConnection(credentials: $credentials)->getCostOfPriceInformation(
            paymentMethod: $this->getIdentifier(method: $this->getMethod()),
            amount: $this->getPrice(),
            fetch: false,
            iframe: true
        );
    }

    /**
     * Retrieve payment method to base API query on.
     *
     * @return PaymentMethodInterface
     * @throws NoSuchEntityException
     */
    protected function getMethod(): PaymentMethodInterface
    {
        return $this->methodRepository->getByCode(
            code: $this->request->getParam(key: 'code')
        );
    }

    /**
     * Retrieve price to base API query on
     *
     * @return int
     */
    private function getPrice(): int
    {
        return (int) ceil(num: (float) $this->request->getParam(key: 'price'));
    }

    /**
     * Retrieve payment method identifier for API call to acquire HTML. Please
     * note that an empty string would make ECom fetch HTML for all available
     * payment methods (using the credentials supplied with initializing the
     * connection instance), which is desirable for DK.
     *
     * @param PaymentMethodInterface $method
     * @return string
     */
    private function getIdentifier(
        PaymentMethodInterface $method
    ): string {
        return $method->getSpecificCountry() !== 'DK' ?
            (string)$method->getIdentifier() :
            '';
    }
}
