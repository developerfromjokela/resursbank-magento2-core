<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Setup\Patch\Data;

use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use function is_array;
use function is_string;

/**
 * Map old config paths to new config paths.
 *
 * Alters Resurs Bank database entries in the "core_config_data" table.
 */
class RemapConfigPaths implements DataPatchInterface
{
    /**
     * Old config section.
     */
    private const OLD_SECTION = 'resursbank_checkout';

    /**
     * New config section.
     */
    private const NEW_SECTION = 'resursbank';

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
    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        // Update 'path' to match new config layout.
        foreach ($this->getMap() as $old => $new) {
            $this->moduleDataSetup->getConnection()->update(
                $this->moduleDataSetup->getTable('core_config_data'),
                ['path' => new Expression("'${new}'")],
                ['path = ?' => $old]
            );
        }
        
        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    /**
     * Retrieve map of old -> new settings paths (excluding their sections
     * since this is the same for all of them).
     *
     * If any of the newer setting paths exists, they will be filtered out of
     * the resulting array, leaving only old paths. This is to prevent MySQL
     * errors when running setup:upgrade or any other command that wants to
     * apply patches.
     *
     * @return string[]
     */
    private function getMap(): array
    {
        $oldSection = self::OLD_SECTION;
        $newSection = self::NEW_SECTION;
        $result = [];

        foreach ($this->getKeys() as $old => $new) {
            $select = $this->moduleDataSetup->getConnection()->select();
            $select->from('core_config_data', 'path');

            $this->moduleDataSetup
                ->getConnection()
                ->select()
                ->exists($select, "path = '${oldSection}/${old}'");

            $statement = $select->assemble();

            $fetch = (is_string($statement)) ?
                $this->moduleDataSetup->getConnection()->fetchRow($statement) :
                null;

            // If old path exists, map it to its new path.
            if (is_array($fetch) && !empty($fetch)) {
                $result["${oldSection}/${old}"] = "${newSection}/${new}";
            }
        }

        return $result;
    }

    /**
     * Retrieve path renaming map.
     *
     * @return string[]
     */
    protected function getKeys(): array
    {
        /**
         * NOTE: Some values appear the same, but the section have changed
         * for them from 'resursbank_checkout' to 'resursbank'.
         *
         * NOTE: API flow and environment values have shifted from precious
         * modules, thus updating their paths may cause undesired behaviour.
         */
        return [
            'api/username_test' => 'api/username_1',
            'api/username_prod' => 'api/username_0',
            'api/password_test' => 'api/password_1',
            'api/password_prod' => 'api/password_0',
            'api/environment' => 'api/environment',
            'api/flow' => 'api/flow',
            'methods/auto_sync_method' => 'api/auto_sync_data',
            'debug/enabled' => 'debug/enabled',
            'advanced/reuse_erroneously_created_orders' => 'advanced/reuse_erroneously_created_orders',
            'advanced/round_tax_percentage' => 'advanced/round_tax_percentage'
        ];
    }
}
