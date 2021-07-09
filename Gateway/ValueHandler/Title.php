<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\ValueHandler;

use Resursbank\Core\Helper\ValueHandlerSubjectReader;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;


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
