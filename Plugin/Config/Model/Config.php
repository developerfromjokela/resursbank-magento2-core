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
use function is_array;

/**
 * Generates the cron schedule for the clean orders job.
 */
class Config
{
    /** @var string */
    private const CRON_STRING_PATH = 'crontab/default/jobs/resursbank_core_clean_orders/schedule/cron_expr';

    /**
     * @param ConfigResourceModel $configResourceModel
     */
    public function __construct(
        private readonly ConfigResourceModel $configResourceModel
    ) {
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
        $scopeId = $subject->getScopeId();

        $cleanOrdersFrequency = $subject->getDataByPath(
            path: 'groups/resursbank_section/groups/advanced/fields/clean_orders_frequency'
        );
        $cleanOrdersTime = $subject->getDataByPath(
            path: 'groups/resursbank_section/groups/advanced/fields/clean_orders_time'
        );

        if (is_array(value: $cleanOrdersFrequency) &&
            is_array(value: $cleanOrdersTime) &&
            $cleanOrdersFrequency['value'] !== null &&
            is_array(value: $cleanOrdersTime['value'])
        ) {
            $frequency = $cleanOrdersFrequency['value'];
            $time = $cleanOrdersTime['value'];

            // phpcs:ignore
            $cronExpression = implode(separator: ' ', array: [
                (int)$time[1],
                (int)$time[0],
                $frequency === CronFrequency::CRON_MONTHLY ? '1' : '*',
                '*',
                $frequency === CronFrequency::CRON_WEEKLY ? '1' : '*'
            ]);

            $this->configResourceModel->saveConfig(
                path: self::CRON_STRING_PATH,
                value: $cronExpression,
                scope: $scopeType,
                scopeId: $scopeId
            );
        }

        return $subject;
    }
}
