<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * @package Resursbank\Core\Setup\Patch\Data
 */
class RemapConfigPaths implements DataPatchInterface
{
    /**
     * Old config section.
     */
    public const OLD_SECTION = 'resursbank_checkout';

    /**
     * New config section.
     */
    public const NEW_SECTION = 'resursbank';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        foreach ($this->getMap() as $old => $new) {
            $this->moduleDataSetup->getConnection()->update(
                $this->moduleDataSetup->getTable('core_config_data'),
                ['path' => (self::NEW_SECTION . '/' . $new)],
                ['path = ?' => (self::OLD_SECTION . '/' . $old)]
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Retrieve map of old -> new settings paths (excluding their sections
     * since this is the same for all of them).
     *
     * @return string[]
     */
    private function getMap(): array
    {
        return [
            'api/environment' => 'api/environment',
            'api/username_test' => 'api/username_1',
            'api/username_prod' => 'api/username_0',
            'api/password_test' => 'api/password_1',
            'api/password_prod' => 'api/password_0',
            'debug/enabled' => 'api/debug',
            'advanced/round_tax_percentage' => 'api/round_tax_percentage',
            'methods/auto_sync_method' => 'methods/auto_sync_method'
        ];
    }
}
