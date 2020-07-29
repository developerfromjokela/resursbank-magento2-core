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
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\RBEcomPHP\ResursBank;
use Resursbank\Core\Gateway\Http\TransferFactory;

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
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @param Api $api
     * @param TransferFactory $transferFactory
     */
    public function __construct(
        Api $api,
        TransferFactory $transferFactory
    ) {
        $this->api = $api;
        $this->transferFactory = $transferFactory;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws LocalizedException
     */
    public function placeRequest(
        TransferInterface $transferObject
    ): array {
        return $this->execute(
            $this->transferFactory->getCredentials(
                $transferObject->getClientConfig()
            ),
            $this->transferFactory->getReference(
                $transferObject->getBody()
            )
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
}
