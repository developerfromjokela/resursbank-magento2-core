<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Controller\Adminhtml\Data;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Ecom;
use Resursbank\Core\Helper\Scope as ScopeHelper;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Api\Environment as EnvironmentEnum;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Store\GetStoresRequest;
use Resursbank\Ecom\Module\Store\Http\GetStoresController;
use Resursbank\Core\Helper\Log;
use Throwable;

/**
 * This controller fetches a list of stores from the API using the credentials
 * supplied in the HTTP POST request.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Stores extends GetStoresController implements HttpPostActionInterface
{
    /**
     * @param Log $log
     * @param JsonFactory $jsonFactory
     * @param Config $config
     * @param ScopeHelper $scope
     * @param Ecom $ecom
     */
    public function __construct(
        protected readonly Log $log,
        protected readonly JsonFactory $jsonFactory,
        protected readonly Config $config,
        protected readonly ScopeHelper $scope,
        protected readonly Ecom $ecom
    ) {
    }

    /**
     * Fetch list of available stores.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        try {
            $requestData = $this->getRequestData();

            // Establish Ecom connection with the credentials in HTTP request.
            $this->ecom->connect(
                jwtAuth: new Jwt(
                    clientId: $requestData->clientId,
                    clientSecret: $requestData->clientSecret,
                    scope: $this->getScope(),
                    grantType: GrantType::CREDENTIALS
                ),
                env: $requestData->environment,
                scope: $this->getScope()
            );

            $data = $this->getData();
        } catch (AuthException) {
            $data = ['error' => __('rb-api-connection-failed-bad-credentials')];
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
            $data = ['error' => __('rb-get-stores-could-not-fetch')];
        }

        // NOTE: Cannot submit data directly to create, won't be returned.
        $result = $this->jsonFactory->create();
        $result->setData(data: $data);

        return $result;
    }

    /**
     * Get Ecom scope for API connection.
     *
     * This is a separate method so that it can be overridden by individual
     * API modules as needed to set the correct scope when connecting.
     *
     * @return Scope
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getScope(): Scope
    {
        return $this->ecom->getScope(
            environment: $this->config->getApiEnvironment(
                scopeCode: $this->scope->getId(),
                scopeType: $this->scope->getType()
            )
        );
    }

    /**
     * Resolve and convert data from HTTP request to fetch stores.
     *
     * @throws HttpException
     */
    public function getRequestData(): GetStoresRequest
    {
        $result = null;
        $data = $this->getInputDataAsStdClass();

        try {
            // Client secret is masked if it's unchanged.
            /* NOTE: We must resolve this before we modify the value of
               $data->environment, since we need it to be 0/1 at this point,
               not "test"/"prod" */
            if (isset($data->clientSecret) &&
                preg_match(pattern: '/^\*+$/', subject: $data->clientSecret)
            ) {
                $data->clientSecret = $this->config->getClientSecret(
                    scopeCode: $this->scope->getId(),
                    scopeType: $this->scope->getType(),
                    environment: (int) $data->environment
                );
            }

            // Convert environment value (specified as 1/0 in deprecated API).
            if (isset($data->environment)) {
                $data->environment = match ((int)$data->environment) {
                    1 => EnvironmentEnum::TEST->value,
                    0 => EnvironmentEnum::PROD->value
                };
            }

            // Create model with request data from converted values.
            $result = $this->getRequestModel(
                model: GetStoresRequest::class,
                data: $data
            );
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        if (!$result instanceof GetStoresRequest) {
            throw new HttpException(
                message: $this->translateError(phraseId: 'invalid-post-data'),
                code: 415
            );
        }

        return $result;
    }

    /**
     * Placeholder for data to be returned. Modified in DI classes.
     *
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}
