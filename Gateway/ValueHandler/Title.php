<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\ValueHandler;

use Resursbank\Core\Helper\ValueHandlerSubjectReader;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

/**
 * Magentos core adapter will resolve the title from the database, regardless of
 * what is specified in the XML. Since our payment methods are stored in a
 * separate table (and injecting values into the core_config_data table would
 * create needless overhead prone to failure) we store the title on the method
 * instance generated during checkout, so we may resolve it here to reflect the
 * correct title on for example the order page in the admin panel.
 */
class Title implements ValueHandlerInterface
{
    public const DEFAULT_TITLE = 'Resurs Bank';

    /**
     * @var ValueHandlerSubjectReader
     */
    private $reader;

    /**
     * @param ValueHandlerSubjectReader $reader
     */
    public function __construct(
        ValueHandlerSubjectReader $reader
    ) {
        $this->reader = $reader;
    }

    /**
     * @inerhitdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(
        array $subject,
        $storeId = null
    ) {
        $title = $this->reader->getAdditional($subject, 'method_title');

        return $title !== null ?
            sprintf('%s (%s)', self::DEFAULT_TITLE, $title):
            self::DEFAULT_TITLE;
    }
}
