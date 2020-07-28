<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * General validation process of gateway command response.
 *
 * @package Resursbank\Core\Gateway\Validator
 */
class General extends AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function validate(
        array $validationSubject
    ): ResultInterface {
        $messages = [];

        /** @var bool $status */
        $successful = $this->wasSuccessful($validationSubject);

        if (!$successful) {
            $messages[] = 'The API request to Resurs Bank failed.';
        }

        return $this->createResult($successful, $messages);
    }

    /**
     * Resolve response status from validation subject.
     *
     * @param array $validationSubject
     * @return bool
     */
    protected function wasSuccessful(
        array $validationSubject
    ): bool {
        return (
            isset($validationSubject['response']['status']) &&
            $validationSubject['response']['status'] === true
        );
    }
}
