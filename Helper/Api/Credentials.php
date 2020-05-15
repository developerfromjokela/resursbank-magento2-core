<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper\Api;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Resursbank\Core\Exception\MissingDataException;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Model\Api\Credentials as CredentialsModel;

/**
 * Business logic for corresponding data model Model\Api\Credentials.
 *
 * @package Resursbank\Core\Helper\Api
 */
class Credentials extends AbstractHelper
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param Context $context
     * @param Config $config
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        Config $config,
        ObjectManagerInterface $objectManager
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;

        parent::__construct($context);
    }

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

    /**
     * Resolve Credentials model instance from config values.
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return CredentialsModel
     * @throws ValidatorException
     */
    public function resolveFromConfig(
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): CredentialsModel {
        /** @var CredentialsModel $credentials */
        $credentials = $this->objectManager->get(CredentialsModel::class);

        return $credentials->setEnvironment(
            $this->config->getEnvironment($scopeCode, $scopeType)
        )->setUsername(
            $this->config->getUsername($scopeCode, $scopeType)
        )->setPassword(
            $this->config->getPassword($scopeCode, $scopeType)
        );
    }
}
