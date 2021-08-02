<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use function is_array;
use JsonException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\PaymentMethods\Converter;
use Resursbank\Core\Model\Api\Credentials as CredentialsModel;
use Resursbank\Core\Model\Payment\Resursbank as Method;
use Resursbank\Core\Model\PaymentMethodFactory;
use Resursbank\Core\Model\PaymentMethodRepository as Repository;
use stdClass;
use function json_decode;
use function strlen;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @noinspection EfferentObjectCouplingInspection
 */
class ValueHandlerSubjectReader extends AbstractHelper
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @param Context $context
     * @param Log $log
     */
    public function __construct(
        Context $context,
        Log $log
    ) {
        $this->log = $log;

        parent::__construct($context);
    }

    public function getAdditional(
        array $subject,
        string $key
    ) {
        $result = null;

        if (isset($subject['payment']) &&
            $subject['payment'] instanceof PaymentDataObject
        ) {
            $result = $subject['payment']
                ->getPayment()
                ->getAdditionalInformation($key);
        }

        return $result;
    }
}
