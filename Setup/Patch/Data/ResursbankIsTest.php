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

/**
 * Converts data for the "resursbank_is_test" column in the "sales_order"
 * table after the data has been migrated.
 *
 * @package Resursbank\Core\Setup\Patch\Data
 */
class ResursbankIsTest implements DataPatchInterface
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
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $tableName = 'sales_order';
        $colName = 'resursbank_is_test';
        $rbColExists = $this->moduleDataSetup
            ->getConnection()
            ->tableColumnExists($tableName, $colName);

        if ($rbColExists) {
            $this->moduleDataSetup->getConnection()->startSetup();

            $table = $this->moduleDataSetup->getTable($tableName);

            // Rows with value "prod" will become 0.
            $this->moduleDataSetup
                ->getConnection()
                ->update(
                    $table,
                    [$colName => new Expression('0')],
                    [($colName . ' = ?') => 'prod']
                );

            // Rows with value "test" will become 1.
            $this->moduleDataSetup
                ->getConnection()
                ->update(
                    $table,
                    [$colName => new Expression('1')],
                    [($colName . ' = ?') => 'test']
                );

            // Rows with an empty value will become null. If we don't convert
            // these rows, Magento will set them as 0.
            $this->moduleDataSetup
                ->getConnection()
                ->update(
                    $table,
                    [$colName => new Expression('null')],
                    [($colName . ' = ?') => '']
                );

            $this->moduleDataSetup->getConnection()->endSetup();
        }
    }
}
