<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper\Api;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Model\Api\Credentials as CredentialsModel;
use Resursbank\RBEcomPHP\ResursBank;
use function array_key_exists;

/**
 * Business logic for corresponding data model Model\Api\Credentials.
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param Config $config
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Config $config,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;

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
     * @throws ValidatorException
     */
    public function getHash(CredentialsModel $model): string
    {
        if ($model->getUsername() === null) {
            throw new ValidatorException(
                __('Unable to generate hash. Missing username.')
            );
        }

        if ($model->getEnvironment() === null) {
            throw new ValidatorException(
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
     * @throws ValidatorException
     */
    public function getMethodSuffix(CredentialsModel $model): string
    {
        if ($model->getUsername() === null) {
            throw new ValidatorException(
                __('Failed to resolve method suffix. Missing username.')
            );
        }

        if ($model->getEnvironment() === null) {
            throw new ValidatorException(
                __('Failed to resolve method suffix. Missing environment.')
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

    /**
     * Returns distinct collection of API credentials from configuration.
     *
     * @return array<CredentialsModel>
     * @throws ValidatorException
     */
    public function getCollection(): array
    {
        $list = [];

        /** @var StoreInterface $store */
        foreach ($this->storeManager->getStores() as $store) {
            /** @var CredentialsModel $credentials */
            $credentials = $this->resolveFromConfig(
                $store->getCode()
            );

            $credentials->setStore($store);

            /** @var string $hash */
            $hash = $this->getHash($credentials);

            // Never process the same API account twice.
            if (!array_key_exists($hash, $list)) {
                $list[$hash] = $credentials;
            }
        }

        return $list;
    }

    /**
     * @param CredentialsModel $credentials
     * @return bool
     */
    public function isTestAccount(CredentialsModel $credentials): bool
    {
        return $credentials->getEnvironment() === ResursBank::ENVIRONMENT_TEST;
    }

    /**
     * NOTE: This method may result in an empty string if no country is
     * configured for the provided store.
     *
     * @param CredentialsModel $credentials
     * @return string
     * @throws StateException
     */
    public function getCountry(CredentialsModel $credentials): string
    {
        if ($credentials->getStore() === null) {
            throw new StateException(
                __('Country code cannot be resolved without a store instance.')
            );
        }

        return strtoupper($this->config->getDefaultCountry(
            $credentials->getStore()->getCode()
        ));
    }
}
