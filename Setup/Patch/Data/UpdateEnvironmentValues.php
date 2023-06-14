<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Resursbank\RBEcomPHP\ResursBank;

/**
 * Update API environment values utilised in the old module, to match updated
 * values in this module.
 */
class UpdateEnvironmentValues implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['value' => ResursBank::ENVIRONMENT_PRODUCTION],
            [
                'path = ?' => 'resursbank/api/environment',
                'value = ?' => 'production'
            ]
        );

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['value' => ResursBank::ENVIRONMENT_TEST],
            [
                'path = ?' => 'resursbank/api/environment',
                'value = ?' => 'test'
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            RemapConfigPaths::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
