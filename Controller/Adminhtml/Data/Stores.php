<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Controller\Adminhtml\Data;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Mapi;
use Resursbank\Core\Helper\Scope as ScopeHelper;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Api\Environment as EnvironmentEnum;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Store\GetStoresRequest;
use Resursbank\Ecom\Module\Store\Http\GetStoresController;
use Resursbank\Ecom\Module\Store\Repository;
use Throwable;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * This controller fetches a list of stores from the API using the credentials
 * supplied in the HTTP POST request.
 */
class Stores extends GetStoresController implements HttpPostActionInterface
{
    /**
     * @param Log $log
     * @param JsonFactory $jsonFactory
     * @param Config $config
     * @param ScopeHelper $scope
     * @param Mapi $mapi
     */
    public function __construct(
        private readonly Log $log,
        private readonly JsonFactory $jsonFactory,
        private readonly Config $config,
        private readonly ScopeHelper $scope,
        private readonly Mapi $mapi
    ) {
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
            // Convert environment value (specified as 1/0 in deprecated API).
            if (isset($data->environment)) {
                $data->environment = match ((int)$data->environment) {
                    1 => EnvironmentEnum::TEST->value,
                    0 => EnvironmentEnum::PROD->value
                };
            }

            // Client secret is masked if it's unchanged.
            if (
                isset($data->clientSecret) &&
                preg_match(pattern: '/^\*+$/', subject: $data->clientSecret)
            ) {
                $data->clientSecret = $this->config->getClientSecret(
                    scopeCode: $this->scope->getId(),
                    scopeType: $this->scope->getType(),
                    environment: (int) $data->environment
                );
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
     * Fetch list of available stores.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        try {
            $requestData = $this->getRequestData();

            // Establish MAPI connection with the credentials in HTTP request.
            $this->mapi->connect(
                jwtAuth: new Jwt(
                    clientId: $requestData->clientId,
                    clientSecret: $requestData->clientSecret,
                    scope: $requestData->environment === EnvironmentEnum::PROD ?
                        Scope::MERCHANT_API :
                        Scope::MOCK_MERCHANT_API,
                    grantType: GrantType::CREDENTIALS
                ),
                env: $requestData->environment
            );

            // Attempt to resolve list of stores.
            $data = Repository::getApi()->getSelectList();
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
}
