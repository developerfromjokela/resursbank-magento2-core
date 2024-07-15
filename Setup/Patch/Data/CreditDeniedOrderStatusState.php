<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Resursbank\Core\Helper\Order;

/**
 * This patch adds state mapping for our custom order status
 * "resursbank_credit_denied" and makes it visible on frontend.
 */
class CreditDeniedOrderStatusState implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * Constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Get list of dependencies.
     *
     * @inheriDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get list of aliases.
     *
     * @inheriDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Apply patch.
     *
     * @inheriDoc
     */
    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $table = $this->moduleDataSetup->getTable(
            tableName: 'sales_order_status_state'
        );

        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            table: $table,
            data: [
                'status' => Order::CREDIT_DENIED_CODE,
                'state' => 'pending_payment',
                'is_default' => 0,
                'visible_on_front' => 1
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }
}
