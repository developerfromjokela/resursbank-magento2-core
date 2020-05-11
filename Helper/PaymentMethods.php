<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\IntegrationException;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\Core\Model\PaymentMethodFactory;

/**
 * @package Resursbank\Core\Helper
 */
class PaymentMethods extends AbstractHelper
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var PaymentMethodFactory
     */
    private $methodFactory;

    /**
     * PaymentMethods constructor.
     * @param Context $context
     * @param Api $api
     * @param PaymentMethodFactory $methodFactory
     */
    public function __construct(
        Context $context,
        Api $api,
        PaymentMethodFactory $methodFactory
    ) {
        $this->api = $api;
        $this->methodFactory = $methodFactory;

        parent::__construct($context);
    }

    /**
     * @param Credentials $credentials
     * @throws Exception
     * @return void
     */
    public function sync(Credentials $credentials): void
    {
        $this->fetch($credentials);
    }

    /**
     * @param Credentials $credentials
     * @return array
     * @throws IntegrationException
     */
    public function fetch(Credentials $credentials): array
    {
        try {
            $methods = $this->api->getConnection($credentials)
                ->getPaymentMethods();
        } catch (Exception $e) {
            throw new IntegrationException(__($e->getMessage()));
        }

        if (!is_array($methods)) {
            throw new IntegrationException(
                __('Failed to fetch payment methods from API. Expected Array.')
            );
        }

        return $methods;
    }
}
