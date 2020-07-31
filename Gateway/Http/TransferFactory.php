<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Http;

use Magento\Framework\Exception\ValidatorException;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\Core\Helper\Api\Credentials as CredentialsHelper;
use function is_string;

/**
 * Generate instance of TransferInterface and apply data required for outgoing
 * API calls to Resurs Bank.
 *
 * @package Resursbank\Core\Gateway\Http
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var CredentialsHelper
     */
    private $credentialsHelper;

    /**
     * @param TransferBuilder $transferBuilder
     * @param CredentialsHelper $credentialsHelper
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        CredentialsHelper $credentialsHelper
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->credentialsHelper = $credentialsHelper;
    }

    /**
     * @inheritdoc
     * @throws ValidatorException
     */
    public function create(
        array $request
    ): TransferInterface {
        return $this->transferBuilder
            ->setClientConfig([
                'credentials' => $this->getCredentials($request)
            ])
            ->setBody([
                'reference' => $this->getReference($request)
            ])
            ->build();
    }

    /**
     * Resolve Credentials instance from anonymous array.
     *
     * @param array $data
     * @return Credentials
     * @throws ValidatorException
     */
    public function getCredentials(
        array $data
    ): Credentials {
        if (!isset($data['credentials'])) {
            throw new ValidatorException(
                __('Missing credentials in request.')
            );
        }

        if (!($data['credentials'] instanceof Credentials)) {
            throw new ValidatorException(
                __('Request credentials must be of type ' . Credentials::class)
            );
        }

        if (!$this->credentialsHelper->hasCredentials($data['credentials'])) {
            throw new ValidatorException(
                __('Incomplete request credentials.')
            );
        }

        return $data['credentials'];
    }

    /**
     * Resolve payment / order reference from anonymous array.
     *
     * @param array $data
     * @return string
     * @throws ValidatorException
     */
    public function getReference(
        array $data
    ): string {
        if (!isset($data['reference'])) {
            throw new ValidatorException(
                __('Missing reference in request.')
            );
        }

        if (!is_string($data['reference'])) {
            throw new ValidatorException(
                __('Requested reference must be a string.')
            );
        }

        if ($data['reference'] === '') {
            throw new ValidatorException(
                __('Missing reference value.')
            );
        }

        return $data['reference'];
    }
}
