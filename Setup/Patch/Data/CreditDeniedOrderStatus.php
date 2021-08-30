<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Setup\Patch\Data;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResourceModel;
use Resursbank\Core\Helper\Order;

/**
 * Add custom order status to reflect credit denied result during checkout.
 */
class CreditDeniedOrderStatus implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var StatusResourceModel
     */
    private StatusResourceModel $statusResourceModel;

    /**
     * @var StatusFactory
     */
    private StatusFactory $statusFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StatusResourceModel $statusResourceModel
     * @param StatusFactory $statusFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StatusResourceModel $statusResourceModel,
        StatusFactory $statusFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->statusResourceModel = $statusResourceModel;
        $this->statusFactory = $statusFactory;
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
     * @throws AlreadyExistsException
     */
    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->statusResourceModel->save(
            $this->statusFactory->create()
                ->setData('status', Order::CREDIT_DENIED_CODE)
                ->setData('label', Order::CREDIT_DENIED_LABEL)
        );

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }
}
