<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Controller\Adminhtml\Data;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
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
    private PaymentMethods $paymentMethods;

    /**
     * @var Credentials
     */
    private Credentials $credentials;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var ResultFactory
     */
    private ResultFactory $resultFactory;

    /**
     * @var RedirectInterface
     */
    private RedirectInterface $redirect;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $message;

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
     *
     * NOTE: We deactivate all methods currently in our table before we sync
     * from the API. The methods need to be retained locally, to ensure the
     * functionality of past orders utilising expired methods.
     */
    public function execute(): ResultInterface
    {
        try {
            $credentialsList = $this->credentials->getCollection();

            if (!empty($credentialsList)) {
                $this->paymentMethods->deactivateMethods();

                foreach ($credentialsList as $credentials) {
                    $this->paymentMethods->sync($credentials);
                }

                $this->message->addSuccessMessage(
                    (string)__(
                        'Successfully synchronized data from Resurs Bank.'
                    )
                );
            } else {
                $this->message->addNoticeMessage(
                    (string)__(
                        'There are no credentials to sync data from Resurs Bank.'
                    )
                );
            }
        } catch (Exception $e) {
            $this->log->exception($e);
            $this->message->addErrorMessage(
                (string) __('Failed to synchronize payment methods.')
            );
        }

        /** @var Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $result->setUrl($this->redirect->getRefererUrl());
    }
}
