<?php

/**
 * @author Pradeep Sanku
 */

namespace Qdos\QdosSync\Model\Config;
class AttributeCronConfig extends \Magento\Framework\App\Config\Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/cron_attribute_sync/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/default/jobs/cron_attribute_sync/run/model';

    protected $_configValueFactory;
    protected $_runModelPath = '';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ){
        $this->_runModelPath = $runModelPath;
        $this->_configValueFactory = $configValueFactory;
        parent::__construct($context,$registry,$config,$cacheTypeList,$resource,$resourceCollection,$data);
    }

    public function afterSave(){
        $frequency = $this->getData('groups/autoSyncAttribute/fields/frequency/value');
        $minutes = $this->getData('groups/autoSyncAttribute/fields/minutes/value');
        $hours = $this->getData('groups/autoSyncAttribute/fields/hours/value');
        $time = $this->getData('groups/autoSyncAttribute/fields/time/value');
        $daysofmonth = $this->getData('groups/autoSyncAttribute/fields/daysofmonth/value');
        $weekday = $this->getData('groups/autoSyncAttribute/fields/weekdays/value');

        $frequencyEveryMinute = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_EVERY_MINUTE;
        $frequencyEveryHour   = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_EVERY_HOUR;
        $frequencyDaily       = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_DAILY;
        $frequencyWeekly      = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_WEEKLY;
        $frequencyMonthly     = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_MONTHLY;

        if(!empty($hours)) {
            $hours = implode(",", $hours);
        }
        if(!empty($minutes)) {
            $minutes = implode(",", $minutes);
        }
        if(!empty($weekday)) {
            $weekday = implode(",", $weekday);
        }
        if(!empty($daysofmonth)) {
            $daysofmonth = implode(",", $daysofmonth);
        }


        $cronExprString ='';
        if($frequency == $frequencyEveryMinute){
            $minutes = (!empty($time[1])) ? '*/'.$time[1] : '*';
            $cronExprString = $minutes.' * * * *';
        }
        if($frequency == $frequencyEveryHour){
            $minutes = (!empty($time[1])) ? $time[1] : '*';
            $hours = (!empty($time[0])) ? '*/'.$time[0] : '*';
            $cronExprString = $minutes.' '.$hours.' * * *';
        }
        if($frequency == $frequencyDaily){
            $cronExprString = $minutes.' '.$hours.' * * *';
        }
        if($frequency == $frequencyMonthly){
            $cronExprString = $minutes.' '.$hours.' '.$daysofmonth.' * *';
        }
        if($frequency == $frequencyWeekly){
            $cronExprString = $minutes.' '.$hours.' * * '.$weekday;
        }

        //echo $cronExprString;die();
        try{
            $this->_configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
            $this->_configValueFactory->create()->load(
                self::CRON_MODEL_PATH,
                'path'
            )->setValue(
                $this->_runModelPath
            )->setPath(
                self::CRON_MODEL_PATH
            )->save();
        }catch(\Exception $e){
            throw new \Exception(__("We can\'t save the cron expression for Attribute."));
        }

        return parent::afterSave();
    }
}