<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Resursbank\Core\Setup\Patch\Data\ResursbankIsTest as ResursbankIsTestData;
use Magento\Framework\DB\Ddl\Table;

/**
 * This patch applies changes to the "resursbank_is_test" column after the
 * data has been migrated and converted from the old "resursbank_environment"
 * column in the "sales_order" table.
 *
 * @package Resursbank\Core\Setup\Patch\Schema
 */
class ResursbankIsTest implements SchemaPatchInterface
{
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
        return [
            ResursbankIsTestData::class
        ];
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
        $tableName = 'sales_order';
        $colName = 'resursbank_is_test';
        $rbColExists = $this->moduleDataSetup
            ->getConnection()
            ->tableColumnExists($tableName, $colName);

        if ($rbColExists) {
            $this->moduleDataSetup->getConnection()->startSetup();

            // Type is updated here rather then in db_schema because
            // otherwise the db_schema change would convert all data in the
            // table to integers on its own, and we need to handle that
            // conversion process ourselves. See dependencies for more info.
            $this->moduleDataSetup
                ->getConnection()
                ->modifyColumn(
                    $tableName,
                    $colName,
                    [
                        'type' => Table::TYPE_BOOLEAN
                    ]
                );

            $this->moduleDataSetup->getConnection()->endSetup();
        }

        return $this;
    }
}
