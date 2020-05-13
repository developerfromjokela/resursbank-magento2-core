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
use Resursbank\RBEcomPHP\ResursBank;

/**
 * @todo 2020-04-27 - In the middle of module separation. Can't use
 *       Ecom constants until a later date because Ecom would become a
 *       dependency that we cannot have during this stage in
 *       development. Will use static values instead.
 */

/**
 * Business logic for corresponding data model Model\Api\Credentials.
 *
 * @package Resursbank\Core\Helper\Api
 */
class Credentials extends AbstractHelper
{
    /**
     * @var string
     */
    public const ENVIRONMENT_CODE_TEST = 'test';

    /**
     * @var string
     */
    public const ENVIRONMENT_CODE_PROD = 'prod';

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
     * Retrieve readable environment code.
     *
     * @param CredentialsModel $model
     * @return string
     * @throws MissingDataException
     */
    public function getEnvironmentCode(CredentialsModel $model): string
    {
        if ($model->getEnvironment() === null) {
            throw new MissingDataException(
                __('Failed to resolve code for environment NULL.')
            );
        }

        return $model->getEnvironment() === 1 ?
            self::ENVIRONMENT_CODE_TEST :
            self::ENVIRONMENT_CODE_PROD;

        // See to-do: 2020-04-27
//        return $this->getEnvironment() === RESURS_ENVIRONMENTS::ENVIRONMENT_TEST ?
//            self::ENVIRONMENT_CODE_TEST :
//            self::ENVIRONMENT_CODE_PROD;
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
            $model->getUsername() . '_' . $this->getEnvironmentCode($model)
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
