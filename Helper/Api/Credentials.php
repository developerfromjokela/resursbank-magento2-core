<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper\Api;

use function array_key_exists;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Model\Api\Credentials as CredentialsModel;

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
    public function hasCredentials(
        CredentialsModel $model
    ): bool {
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
    public function getHash(
        CredentialsModel $model
    ): string {
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
    public function getMethodSuffix(
        CredentialsModel $model
    ): string {
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
        ?string $scopeCode,
        string $scopeType
    ): CredentialsModel {
        $credentials = $this->objectManager->create(CredentialsModel::class);

        $credentials->setEnvironment(
            $this->config->getEnvironment($scopeCode, $scopeType)
        );

        $username = $this->config->getUsername($scopeCode, $scopeType);
        $password = $this->config->getPassword($scopeCode, $scopeType);

        if ($username !== '') {
            $credentials->setUsername($username);
        }

        if ($password !== '') {
            $credentials->setPassword($password);
        }

        $country = $this->config->getDefaultCountry($scopeCode, $scopeType);

        if ($country === '') {
            throw new ValidatorException(
                __('Failed to apply country to Credentials instance.')
            );
        }

        $credentials->setCountry(strtoupper($country));

        return $credentials;
    }

    /**
     * Returns distinct collection of API credentials from configuration.
     *
     * @return array<CredentialsModel>
     * @throws ValidatorException
     */
    public function getCollection(): array
    {
        $result = [];

        // Default scope.
        $collection = [
            $this->resolveFromConfig(
                null,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            )
        ];

        // Website scope.
        foreach ($this->storeManager->getWebsites() as $website) {
            $collection[] = $this->resolveFromConfig(
                $website->getCode(),
                ScopeInterface::SCOPE_WEBSITES
            );
        }

        // Store scope.
        foreach ($this->storeManager->getStores() as $store) {
            $collection[] = $this->resolveFromConfig(
                $store->getCode(),
                ScopeInterface::SCOPE_STORES
            );
        }

        // Filter list (make it contain only unique instances).
        foreach ($collection as $credentials) {
            if ($this->hasCredentials($credentials)) {
                $hash = $this->getHash($credentials);

                // Never process the same API account twice.
                if (!array_key_exists($hash, $result)) {
                    $result[$hash] = $credentials;
                }
            }
        }

        return $result;
    }
}
