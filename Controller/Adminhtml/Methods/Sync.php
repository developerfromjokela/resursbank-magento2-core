<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Controller\Adminhtml\Methods;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;

/**
 * This controller executes the process which synchronizes all available payment
 * methods from Resurs Bank to the corresponding table in the database.
 *
 * @package Resursbank\Checkout\Controller\Adminhtml\Method
 */
class Sync extends Action
{
    /**
     * @var PaymentMethods
     */
    private $paymentMethods;

    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * @var Log
     */
    private $log;

    /**
     * @param Context $context
     * @param PaymentMethods $paymentMethods
     * @param Credentials $credentials
     * @param Log $log
     */
    public function __construct(
        Context $context,
        PaymentMethods $paymentMethods,
        Credentials $credentials,
        Log $log
    ) {
        $this->paymentMethods = $paymentMethods;
        $this->credentials = $credentials;
        $this->log = $log;

        parent::__construct($context);
    }

    /**
     * Synchronize payment methods.
     */
    public function execute()
    {
        try {
            foreach ($this->credentials->getCollection() as $credentials) {
                $this->paymentMethods->sync($credentials);
            }

            // Add success message.
            $this->getMessageManager()->addSuccessMessage(
                'Successfully synchronized payment methods.'
            );
        } catch (Exception $e) {
            $this->log->exception($e);

            // Add error message.
            $this->getMessageManager()->addErrorMessage(
                'Failed to synchronize payment methods.'
            );
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
