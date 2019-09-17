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
        $attributeSync1=$this->getData('groups/autoSyncAttribute/fields/auto_sync_attribute/value');
        $time = $this->getData('groups/autoSyncAttribute/fields/time/value');
        $attributeSync2=$this->getData('groups/autoSyncAttribute/fields/add_new_schedule/value');
        $time1 = $this->getData('groups/autoSyncAttribute/fields/time1/value');
        $attributeSync3=$this->getData('groups/autoSyncAttribute/fields/add_new_sync_attribute_schedule/value');
        $time2 = $this->getData('groups/autoSyncAttribute/fields/time2/value');
        $daysofmonth = $this->getData('groups/autoSyncAttribute/fields/daysofmonth/value');
        $weekday = $this->getData('groups/autoSyncAttribute/fields/weekdays/value');

        $frequencyEveryMinute = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_EVERY_MINUTE;
        $frequencyEveryHour   = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_EVERY_HOUR;
        $frequencyDaily       = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_DAILY;
        $frequencyWeekly      = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_WEEKLY;
        $frequencyMonthly     = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_MONTHLY;

        /*Log code*/
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/syncAttributeSwapnil.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $cronExprMinute = $cronExprHour = $cronExprDay = $cronExprWeek = array();
        if (!in_array(intval($time['1']),$cronExprMinute)){
            $cronExprMinute[] = ($frequency == $frequencyEveryMinute) ? '*/'.intval($time['1']) : intval($time['1']);
        }

        if ($frequency == $frequencyEveryMinute){
            $hour = '*';
        }else{
            $hour = ($frequency == $frequencyEveryHour) ? '*/'.intval($time['0']) : intval($time['0']);
        }

        if (!in_array($hour, $cronExprHour)){
            $cronExprHour[] = $hour;
        }

        $day = ($frequency == $frequencyMonthly) ? $daysofmonth : '*';
        if (!in_array($day,$cronExprDay)){
            $cronExprDay[] = $day;
        }

        $week_day = ($frequency == $frequencyWeekly) ? $weekday : '*';
        if (!in_array($week_day,$cronExprWeek)){
            $cronExprWeek[] = $week_day;
        }

        $cronExprString = join(' ', array(join(',',$cronExprMinute),join(',',$cronExprHour),join(',',$cronExprDay),'*',join(',',$cronExprWeek)));
        
        $cronExprMinute1 = $cronExprHour1 = $cronExprDay1 = $cronExprWeek1 = array();
        if (!in_array(intval($time1['1']),$cronExprMinute1)){
            $cronExprMinute1[] = ($frequency == $frequencyEveryMinute) ? '*/'.intval($time1['1']) : intval($time1['1']);
        }

        if ($frequency == $frequencyEveryMinute){
            $hour1 = '*';
        }else{
            $hour1 = ($frequency == $frequencyEveryHour) ? '*/'.intval($time1['0']) : intval($time1['0']);
        }

        if (!in_array($hour, $cronExprHour)){
            $cronExprHour1[] = $hour1;
        }

        $day1 = ($frequency == $frequencyMonthly) ? $daysofmonth : '*';
        if (!in_array($day1,$cronExprDay1)){
            $cronExprDay1[] = $day1;
        }

        $week_day1 = ($frequency == $frequencyWeekly) ? $weekday : '*';
        if (!in_array($week_day1,$cronExprWeek1)){
            $cronExprWeek1[] = $week_day1;
        }

        $cronExprString1 = join(' ', array(join(',',$cronExprMinute1),join(',',$cronExprHour1),join(',',$cronExprDay1),'*',join(',',$cronExprWeek1)));

        $cronExprMinute2 = $cronExprHour2 = $cronExprDay2 = $cronExprWeek2 = array();
        if (!in_array(intval($time2['1']),$cronExprMinute2)){
            $cronExprMinute2[] = ($frequency == $frequencyEveryMinute) ? '*/'.intval($time2['1']) : intval($time2['1']);
        }

        if ($frequency == $frequencyEveryMinute){
            $hour2 = '*';
        }else{
            $hour2 = ($frequency == $frequencyEveryHour) ? '*/'.intval($time2['0']) : intval($time2['0']);
        }

        if (!in_array($hour2, $cronExprHour)){
            $cronExprHour2[] = $hour2;
        }

        $day2 = ($frequency == $frequencyMonthly) ? $daysofmonth : '*';
        if (!in_array($day2,$cronExprDay2)){
            $cronExprDay2[] = $day2;
        }

        $week_day2 = ($frequency == $frequencyWeekly) ? $weekday : '*';
        if (!in_array($week_day2,$cronExprWeek2)){
            $cronExprWeek2[] = $week_day2;
        }

        $cronExprString2 = join(' ', array(join(',',$cronExprMinute2),join(',',$cronExprHour2),join(',',$cronExprDay2),'*',join(',',$cronExprWeek2)));
        /*Log code*/
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/syncAttributeSwapnil.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->info('in SyncStock : '.$cronExprString." & ".$cronExprString1." & ".$cronExprString2);

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
        if($attributeSync2){
            try{
                $this->_configValueFactory->create()->load(
                    self::CRON_STRING_PATH,
                    'path'
                )->setValue(
                    $cronExprString1
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
        }
        if($attributeSync3){
            try{
                $this->_configValueFactory->create()->load(
                    self::CRON_STRING_PATH,
                    'path'
                )->setValue(
                    $cronExprString2
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
        }

        return parent::afterSave();
    }
}