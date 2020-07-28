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
use function is_string;

/**
 * Create instances of TransferBuilder and apply assembled request data.
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
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * @inheritdoc
     * @throws ValidatorException
     */
    public function create(
        array $request
    ): TransferInterface {
        $this->validate($request);

        return $this->transferBuilder
            ->setClientConfig([
                'credentials' => $request['credentials']
            ])
            ->setBody([
                'reference' => $request['reference']
            ])
            ->build();
    }

    /**
     * @param array $request
     * @throws ValidatorException
     */
    private function validate(
        array $request
    ): void {
        $this->validateCredentials($request);
        $this->validateOrderReference($request);
    }

    /**
     * @param array $request
     * @throws ValidatorException
     */
    private function validateCredentials(
        array $request
    ): void {
        if (!isset($request['credentials'])) {
            throw new ValidatorException(
                __('Missing credentials in request.')
            );
        }

        if (!($request['credentials'] instanceof Credentials)) {
            throw new ValidatorException(
                __('Request credentials must be of type ' . Credentials::class)
            );
        }
    }

    /**
     * @param array $request
     * @throws ValidatorException
     */
    private function validateOrderReference(
        array $request
    ): void {
        if (!isset($request['reference'])) {
            throw new ValidatorException(
                __('Missing order reference in request.')
            );
        }

        if (!is_string($request['reference'])) {
            throw new ValidatorException(
                __('Requested order reference must be a string.')
            );
        }
    }
}
