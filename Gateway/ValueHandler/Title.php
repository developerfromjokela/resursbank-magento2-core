<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\ValueHandler;

use Exception;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Resursbank\Core\Api\ValueHandlerInterface;
use Resursbank\Core\Helper\Log;

/**
 * Magento's core adapter will resolve the title from the database, regardless
 * of what is specified in the array assembled from the XML (see
 * Resursbank\Core\Plugin\Payment\Helper\Data). Since our payment methods are
 * stored in a separate table (and injecting values into the core_config_data
 * table would create needless overhead prone to failure) we store the title on
 * the method instance generated during checkout, so we may resolve it here to
 * reflect the correct title on for example the order page in the admin panel.
 */
class Title implements ValueHandlerInterface
{
    /**
     * @var string
     */
    public const DEFAULT_TITLE = 'Resurs Bank';

    /**
     * @param Log $log
     */
    public function __construct(
        private readonly Log $log
    ) {
    }

    /**
     * @inheritdoc
     *
     * @param array $subject
     * @param int|null $storeId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(
        array $subject,
        $storeId = null
    ): string {
        $result = self::DEFAULT_TITLE;

        try {
            if (isset($subject['payment']) &&
                $subject['payment'] instanceof PaymentDataObject
            ) {
                $title = (string) $subject['payment']->getPayment()
                    ->getAdditionalInformation('method_title');

                if ($title !== '') {
                    $result = $title;
                }
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
