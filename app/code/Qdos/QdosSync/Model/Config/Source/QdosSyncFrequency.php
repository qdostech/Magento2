<?php
namespace Qdos\QdosSync\Model\Config\Source;

class QdosSyncFrequency implements \Magento\Framework\Option\ArrayInterface
{
    protected static $_options;

    const CRON_EVERY_MINUTE = 'EM';
    const CRON_EVERY_HOUR = 'EH';
    const CRON_DAILY = 'D';
    const CRON_WEEKLY = 'W';
    const CRON_MONTHLY = 'M';

    public function toOptionArray()
    {
        if (!self::$_options) {
            self::$_options = [
                ['label' => __('Every Minute'), 'value' => self::CRON_EVERY_MINUTE],
                ['label' => __('Every Hour'), 'value' => self::CRON_EVERY_HOUR],
                ['label' => __('At exact time every day'), 'value' => self::CRON_DAILY],
                ['label' => __('Weekly'), 'value' => self::CRON_WEEKLY],
                ['label' => __('Monthly'), 'value' => self::CRON_MONTHLY],
            ];
        }
        return self::$_options;
    }
}
