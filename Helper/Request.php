<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Resursbank\Core\Exception\InvalidDataException;

class Request extends AbstractHelper
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param Context $context
     * @param RequestInterface $request
     */
    public function __construct(
        Context $context,
        RequestInterface $request
    ) {
        $this->request = $request;

        parent::__construct($context);
    }

    /**
     * @return int
     * @throws InvalidDataException
     */
    public function getQuoteId(): int
    {
        $quoteId = (int) $this->request->getParam('quote_id');

        if ($quoteId === 0) {
            throw new InvalidDataException(__(
                'Quote ID 0 (zero) is an invalid ID.'
            ));
        }

        return $quoteId;
    }
}
