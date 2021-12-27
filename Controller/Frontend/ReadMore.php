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

class ReadMore implements HttpGetActionInterface
{
    /**
     * @var LogInterface
     */
    protected LogInterface $log;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var ResultFactory
     */
    protected ResultFactory $resultFactory;

    /**
     * @var PaymentMethodRepository
     */
    protected PaymentMethodRepository $methodRepository;

    /**
     * @var Api
     */
    protected Api $api;

    /**
     * @var Credentials
     */
    protected Credentials $credentials;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

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
        LogInterface $log,
        RequestInterface $request,
        ResultFactory $resultFactory,
        PaymentMethodRepository $methodRepository,
        Api $api,
        Credentials $credentials,
        StoreManagerInterface $storeManager
    ) {
        $this->log = $log;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->methodRepository = $methodRepository;
        $this->api = $api;
        $this->credentials = $credentials;
        $this->storeManager = $storeManager;
    }

    /**
     * @throws Exception
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $result->setData(['html' => $this->getHtml()]);
        } catch (Exception $e) {
            $this->log->exception($e);

            $result->setData([
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
     * @return string
     * @throws NoSuchEntityException
     * @throws ValidatorException
     * @throws Exception
     */
    private function getHtml(): string
    {
        $store = $this->storeManager->getStore();
        $credentials = $this->credentials->resolveFromConfig(
            $store->getCode(),
            ScopeInterface::SCOPE_STORES
        );

        $connection = $this->api->getConnection($credentials);

        return $connection->getCostOfPriceInformation(
            $this->getIdentifier($this->getMethod()),
            $this->getPrice(),
            false,
            true
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
            $this->request->getParam('code')
        );
    }

    /**
     * Retrieve price to base API query on
     *
     * @return int
     */
    private function getPrice(): int
    {
        return (int) ceil((float) $this->request->getParam('price'));
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
