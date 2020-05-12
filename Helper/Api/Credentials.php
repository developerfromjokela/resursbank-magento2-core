<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper\Api;

use Resursbank\Core\Exception\MissingDataException;
use Resursbank\Core\Model\Api\Credentials as CredentialsModel;

/**
 * Business logic for corresponding data model Model\Api\Credentials.
 *
 * @package Resursbank\Core\Helper\Api
 */
class Credentials
{
    /**
     * @param CredentialsModel $model
     * @return bool
     */
    public function hasCredentials(CredentialsModel $model): bool
    {
        return (
            $model->getUsername() !== null &&
            $model->getPassword() !== null
        );
    }

    /**
     * Retrieve hash value based on credentials.
     *
     * @param CredentialsModel $model
     * @return string
     * @throws MissingDataException
     */
    public function getHash(CredentialsModel $model): string
    {
        if ($model->getUsername() === null) {
            throw new MissingDataException(
                __('Unable to generate hash. Missing username.')
            );
        }

        if ($model->getEnvironment() === null) {
            throw new MissingDataException(
                __('Unable to generate hash. Missing environment.')
            );
        }

        return sha1(
            $model->getUsername() .
            $model->getEnvironment()
        );
    }

    /**
     * Retrieve readable unique method code suffix.
     *
     * @param CredentialsModel $model
     * @return string - Returns a lowercase string.
     * @throws MissingDataException
     */
    public function getMethodSuffix(CredentialsModel $model): string
    {
        if ($model->getUsername() === null) {
            throw new MissingDataException(
                __('Failed to resolve method suffix. Missing username.')
            );
        }

        return strtolower(
            $model->getUsername() . '_' . $model->getEnvironment()
        );
    }
}
