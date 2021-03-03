<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Controller\Adminhtml\Methods;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;

/**
 * This controller executes the process which synchronizes all available payment
 * methods from Resurs Bank to the corresponding table in the database.
 */
class Sync implements HttpGetActionInterface
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
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var ManagerInterface
     */
    private $message;

    /**
     * @param PaymentMethods $paymentMethods
     * @param Credentials $credentials
     * @param Log $log
     * @param ResultFactory $resultFactory
     * @param RedirectInterface $redirect
     * @param ManagerInterface $message
     */
    public function __construct(
        PaymentMethods $paymentMethods,
        Credentials $credentials,
        Log $log,
        ResultFactory $resultFactory,
        RedirectInterface $redirect,
        ManagerInterface $message
    ) {
        $this->paymentMethods = $paymentMethods;
        $this->credentials = $credentials;
        $this->log = $log;
        $this->resultFactory = $resultFactory;
        $this->redirect = $redirect;
        $this->message = $message;
    }

    /**
     * Synchronize payment methods.
     */
    public function execute(): ResultInterface
    {
        try {
            foreach ($this->credentials->getCollection() as $credentials) {
                $this->paymentMethods->sync($credentials);
            }

            $this->message->addSuccessMessage(
                __('Successfully synchronized payment methods.')
            );
        } catch (Exception $e) {
            $this->log->exception($e);
            $this->message->addErrorMessage(
                __('Failed to synchronize payment methods.')
            );
        }
        

        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT)
            ->setUrl($this->redirect->getRefererUrl());
    }
}
