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
 * Converts data from "resursbank_environment" to "resursbank_is_test" column
 * in the "sales_order" table.
 */
class MigrateEnvironment implements DataPatchInterface
{
    /**
     * Table this patch targets.
     */
    private const TABLE = 'sales_order';

    /**
     * New column containing environment information (what API environment a
     * purchase was conducted in). This data enables us to fetch data from the
     * correct API environment when handling an order, regardless of currently
     * configured environment.
     */
    private const IS_TEST_COL = 'resursbank_is_test';

    /**
     * Old column containing environment information. We changed it from a
     * varchar to a boolean to minimise our footprint in database transactions.
     */
    private const ENV_COL = 'resursbank_environment';

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
        if ($this->environmentColumnExists() && $this->isTestColumnExists()) {
            $this->moduleDataSetup->getConnection()->startSetup();
            $table = $this->moduleDataSetup->getTable(self::TABLE);

            /**
             * In rows where column "resursbank_environment" has a value of
             * "prod", assign value "0" to column "resursbank_is_test".
             */
            $this->moduleDataSetup
                ->getConnection()
                ->update(
                    $table,
                    [self::IS_TEST_COL => new Expression('0')],
                    [(self::ENV_COL . ' = ?') => 'prod']
                );

            /**
             * In rows where column "resursbank_environment" has a value of
             * "test", assign value "1" to column "resursbank_is_test".
             */
            $this->moduleDataSetup
                ->getConnection()
                ->update(
                    $table,
                    [self::IS_TEST_COL => new Expression('1')],
                    [(self::ENV_COL . ' = ?') => 'test']
                );

            /**
             * In rows where column "resursbank_environment" has no value we
             * assign value NULL to column "resursbank_is_test".
             *
             * NOTE: Magento will automatically assign value "0" to column
             * "resursbank_is_test" when its created. Thus we are required to
             * clear it manually this way.
             */
            $this->moduleDataSetup
                ->getConnection()
                ->update(
                    $table,
                    [self::IS_TEST_COL => new Expression('null')],
                    [(self::ENV_COL . ' = ?') => '']
                );

            $this->moduleDataSetup->getConnection()->endSetup();
        }

        return $this;
    }

    /**
     * Confirm column "resursbank_is_test" exists in "sales_order" table.
     *
     * @return bool
     */
    private function isTestColumnExists(): bool
    {
        return $this->moduleDataSetup
            ->getConnection()
            ->tableColumnExists(self::TABLE, self::IS_TEST_COL);
    }

    /**
     * Confirm column "resursbank_environment" exists in "sales_order" table.
     *
     * @return bool
     */
    private function environmentColumnExists(): bool
    {
        return $this->moduleDataSetup
            ->getConnection()
            ->tableColumnExists(self::TABLE, self::ENV_COL);
    }
}
