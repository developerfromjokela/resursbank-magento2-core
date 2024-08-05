<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Config\Model;

use Magento\Config\Model\Config as ConfigModel;
use Magento\Cron\Model\Config\Source\Frequency as CronFrequency;
use Magento\Config\Model\ResourceModel\Config as ConfigResourceModel;
use Resursbank\Core\Helper\Config as ConfigHelper;

/**
 * Generates the cron schedule for the clean orders job.
 */
class Config
{
    /** @var string */
    private const CRON_STRING_PATH = 'crontab/default/jobs/resursbank_core_clean_orders/schedule/cron_expr';
    private ConfigResourceModel $configResourceModel;
    private ConfigHelper $configHelper;

    /**
     * @param ConfigResourceModel $configResourceModel
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigResourceModel $configResourceModel,
        ConfigHelper $configHelper
    ) {
        $this->configHelper = $configHelper;
        $this->configResourceModel = $configResourceModel;
    }

    /**
     * Attempts to pick up changes in config and use them for updated crontab.
     *
     * @param ConfigModel $subject
     * @return ConfigModel
     */
    public function afterSave(ConfigModel $subject): ConfigModel
    {
        $scopeType = $subject->getScope();
        $scopeCode = $subject->getScopeCode();
        $scopeId = $subject->getScopeId();

        $frequency = $this->configHelper->getCleanOrdersFrequency(
            $scopeCode,
            $scopeType
        );
        $time = $this->configHelper->getCleanOrdersTime(
            $scopeCode,
            $scopeType
        );

        if (!empty($time) &&
            !empty($frequency)) {
            // phpcs:ignore
            $cronExpression = implode(' ', [
                (int)$time[1],
                (int)$time[0],
                $frequency === CronFrequency::CRON_MONTHLY ? '1' : '*',
                '*',
                $frequency === CronFrequency::CRON_WEEKLY ? '1' : '*'
            ]);

            $this->configResourceModel->saveConfig(
                self::CRON_STRING_PATH,
                $cronExpression,
                $scopeType,
                $scopeId
            );
        }

        return $subject;
    }
}
