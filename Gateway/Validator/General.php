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
 * General validation rules for API call responses.
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
     * Resolve response status from anonymous array.
     *
     * @param array $data
     * @return bool
     */
    protected function wasSuccessful(
        array $data
    ): bool {
        return (
            isset($data['response']['status']) &&
            $data['response']['status'] === true
        );
    }
}
