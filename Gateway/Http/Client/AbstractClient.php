<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Http\Client;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Resursbank\Core\Exception\MissingCredentialsException;
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\RBEcomPHP\ResursBank;
use function is_string;

/**
 * Base for all commands including API calls.
 *
 * @package Resursbank\Core\Gateway\Http\Client
 */
abstract class AbstractClient implements ClientInterface, EcomClientInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @param Api $api
     */
    public function __construct(
        Api $api
    ) {
        $this->api = $api;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws LocalizedException
     * @throws MissingCredentialsException
     */
    public function placeRequest(
        TransferInterface $transferObject
    ): array {
        return $this->execute(
            $this->getCredentials($transferObject->getClientConfig()),
            $this->getReference($transferObject->getBody())
        );
    }

    /**
     * Retrieve connection adapter to Resurs Bank API.
     *
     * @param Credentials $credentials
     * @return ResursBank
     * @throws Exception
     */
    protected function getAdapter(
        Credentials $credentials
    ): ResursBank {
        return $this->api->getConnection($credentials);
    }

    /**
     * Resolve API credentials from transfer object.
     *
     * @param array $clientConfig
     * @return Credentials
     * @throws MissingCredentialsException
     */
    protected function getCredentials(
        array $clientConfig
    ): Credentials {
        if (!isset($clientConfig['credentials']) ||
            !($clientConfig['credentials'] instanceof Credentials)
        ) {
            throw new MissingCredentialsException(
                'Credentials in client config must be instance of ' .
                'Resursbank\Core\Model\Api\Credentials.'
            );
        }

        return $clientConfig['credentials'];
    }

    /**
     * Resolve payment reference from transfer object.
     *
     * @param array $body
     * @return string
     * @throws LocalizedException
     */
    protected function getReference(
        array $body
    ): string {
        if (!isset($body['reference'])) {
            throw new LocalizedException(
                __('Missing payment reference in request body.')
            );
        }

        if (!is_string($body['reference'])) {
            throw new LocalizedException(
                __('Payment reference must be a string.')
            );
        }

        return $body['reference'];
    }
}
