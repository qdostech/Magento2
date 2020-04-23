<?php

/**
 * @author Pradeep Sanku
 */

namespace Qdos\Syncproducer\Model\Config;
class ProducersCronConfig extends \Magento\Framework\App\Config\Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/cron_producer_sync/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/default/jobs/cron_producer_sync/run/model';

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
        $frequency = $this->getData('groups/autoSyncProducers/fields/frequency/value');
        $producerSync=$this->getData('groups/autoSyncProducers/fields/auto_sync_producers/value');
        $time = $this->getData('groups/autoSyncProducers/fields/time/value');
        $producerSync2=$this->getData('groups/autoSyncProducers/fields/add_new_sync_producers_schedule/value');
        $time2 = $this->getData('groups/autoSyncProducers/fields/time2/value');
        $producerSync3=$this->getData('groups/autoSyncProducers/fields/add_another_new_sync_producers_schedule/value');

        $time3 = $this->getData('groups/autoSyncProducers/fields/time3/value');

        $producerSync4=$this->getData('groups/autoSyncProducers/fields/add_another_new_one_sync_producers_schedule/value');

        $time4 = $this->getData('groups/autoSyncProducers/fields/time4/value');

        $producerSync5=$this->getData('groups/autoSyncProducers/fields/add_another_new_two_sync_producers_schedule/value');

        $time5 = $this->getData('groups/autoSyncProducers/fields/time5/value');
        $daysofmonth = $this->getData('groups/autoSyncProducers/fields/daysofmonth/value');
        $weekday = $this->getData('groups/autoSyncProducers/fields/weekdays/value');

        $frequencyEveryMinute = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_EVERY_MINUTE;
        $frequencyEveryHour   = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_EVERY_HOUR;
        $frequencyDaily		  = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_DAILY;
        $frequencyWeekly	  = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_WEEKLY;
        $frequencyMonthly	  = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_MONTHLY;

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

        //another cron by Swapnil Shinde
        // $cronExprMinute1 = $cronExprHour1 = $cronExprDay1 = $cronExprWeek1 = array();
        // if (!in_array(intval($time1['1']),$cronExprMinute1)){
        //     $cronExprMinute1[] = ($frequency == $frequencyEveryMinute) ? '*/'.intval($time1['1']) : intval($time1['1']);
        // }

        // if ($frequency == $frequencyEveryMinute){
        //     $hour1 = '*';
        // }else{
        //     $hour1 = ($frequency == $frequencyEveryHour) ? '*/'.intval($time1['0']) : intval($time1['0']);
        // }

        // if (!in_array($hour1, $cronExprHour1)){
        //     $cronExprHour1[] = $hour1;
        // }

        // $day = ($frequency == $frequencyMonthly) ? $daysofmonth : '*';
        // if (!in_array($day,$cronExprDay1)){
        //     $cronExprDay1[] = $day;
        // }

        // $week_day1 = ($frequency == $frequencyWeekly) ? $weekday : '*';
        // if (!in_array($week_day1,$cronExprWeek1)){
        //     $cronExprWeek1[] = $week_day1;
        // }

        // $cronExprString1 = join(' ', array(join(',',$cronExprMinute1),join(',',$cronExprHour1),join(',',$cronExprDay1),'*',join(',',$cronExprWeek1)));

        //another cron by Swapnil Shinde
        $cronExprMinute2 = $cronExprHour2 = $cronExprDay2 = $cronExprWeek2 = array();
        if (!in_array(intval($time2['1']),$cronExprMinute2)){
            $cronExprMinute2[] = ($frequency == $frequencyEveryMinute) ? '*/'.intval($time2['1']) : intval($time2['1']);
        }

        if ($frequency == $frequencyEveryMinute){
            $hour2 = '*';
        }else{
            $hour2 = ($frequency == $frequencyEveryHour) ? '*/'.intval($time2['0']) : intval($time2['0']);
        }

        if (!in_array($hour2, $cronExprHour2)){
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

        //another cron by Swapnil Shinde
        $cronExprMinute3 = $cronExprHour3 = $cronExprDay3 = $cronExprWeek3 = array();
        if (!in_array(intval($time3['1']),$cronExprMinute3)){
            $cronExprMinute3[] = ($frequency == $frequencyEveryMinute) ? '*/'.intval($time3['1']) : intval($time3['1']);
        }

        if ($frequency == $frequencyEveryMinute){
            $hour3 = '*';
        }else{
            $hour3 = ($frequency == $frequencyEveryHour) ? '*/'.intval($time3['0']) : intval($time3['0']);
        }

        if (!in_array($hour3, $cronExprHour3)){
            $cronExprHour3[] = $hour3;
        }

        $day3 = ($frequency == $frequencyMonthly) ? $daysofmonth : '*';
        if (!in_array($day3,$cronExprDay3)){
            $cronExprDay3[] = $day3;
        }

        $week_day3 = ($frequency == $frequencyWeekly) ? $weekday : '*';
        if (!in_array($week_day3,$cronExprWeek3)){
            $cronExprWeek3[] = $week_day3;
        }

        $cronExprString3 = join(' ', array(join(',',$cronExprMinute3),join(',',$cronExprHour3),join(',',$cronExprDay3),'*',join(',',$cronExprWeek3)));


        ######################################

          $cronExprMinute4 = $cronExprHour4 = $cronExprDay4 = $cronExprWeek4 = array();
        if (!in_array(intval($time4['1']),$cronExprMinute4)){
            $cronExprMinute4[] = ($frequency == $frequencyEveryMinute) ? '*/'.intval($time4['1']) : intval($time4['1']);
        }

        if ($frequency == $frequencyEveryMinute){
            $hour4 = '*';
        }else{
            $hour4 = ($frequency == $frequencyEveryHour) ? '*/'.intval($time4['0']) : intval($time4['0']);
        }

        if (!in_array($hour4, $cronExprHour4)){
            $cronExprHour4[] = $hour4;
        }

        $day4 = ($frequency == $frequencyMonthly) ? $daysofmonth : '*';
        if (!in_array($day4,$cronExprDay4)){
            $cronExprDay4[] = $day4;
        }

        $week_day4 = ($frequency == $frequencyWeekly) ? $weekday : '*';
        if (!in_array($week_day4,$cronExprWeek4)){
            $cronExprWeek4[] = $week_day4;
        }

        $cronExprString4 = join(' ', array(join(',',$cronExprMinute4),join(',',$cronExprHour4),join(',',$cronExprDay4),'*',join(',',$cronExprWeek4)));
        ########################################
 ######################################

          $cronExprMinute5 = $cronExprHour5 = $cronExprDay5 = $cronExprWeek5 = array();
        if (!in_array(intval($time5['1']),$cronExprMinute5)){
            $cronExprMinute5[] = ($frequency == $frequencyEveryMinute) ? '*/'.intval($time5['1']) : intval($time5['1']);
        }

        if ($frequency == $frequencyEveryMinute){
            $hour5 = '*';
        }else{
            $hour5 = ($frequency == $frequencyEveryHour) ? '*/'.intval($time5['0']) : intval($time5['0']);
        }

        if (!in_array($hour5, $cronExprHour5)){
            $cronExprHour5[] = $hour5;
        }

        $day5 = ($frequency == $frequencyMonthly) ? $daysofmonth : '*';
        if (!in_array($day5,$cronExprDay5)){
            $cronExprDay5[] = $day5;
        }

        $week_day5 = ($frequency == $frequencyWeekly) ? $weekday : '*';
        if (!in_array($week_day5,$cronExprWeek5)){
            $cronExprWeek5[] = $week_day5;
        }

        $cronExprString5 = join(' ', array(join(',',$cronExprMinute5),join(',',$cronExprHour5),join(',',$cronExprDay5),'*',join(',',$cronExprWeek5)));
        ########################################

        /*Log code*/
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/producersync.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->info('Cron : '.$cronExprString.'Cron 1: '.$cronExprString.'Cron 2: '.$cronExprString2.'Cron 3: '.$cronExprString3.'Cron 4: '.$cronExprString4.'Cron 5: '.$cronExprString5);
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
            throw new \Exception(__("We can\'t save the cron expression for Category."));
        }
        if($producerSync2){
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
                throw new \Exception(__("We can\'t save the cron expression for Category."));
            }
        }
        if($producerSync3){
            try{
                $this->_configValueFactory->create()->load(
                    self::CRON_STRING_PATH,
                    'path'
                )->setValue(
                    $cronExprString3
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
                throw new \Exception(__("We can\'t save the cron expression for Category."));
            }
        }

        if($producerSync4){
            try{
                $this->_configValueFactory->create()->load(
                    self::CRON_STRING_PATH,
                    'path'
                )->setValue(
                    $cronExprString4
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
                throw new \Exception(__("We can\'t save the cron expression for Category."));
            }
        }

        if($producerSync5){
            try{
                $this->_configValueFactory->create()->load(
                    self::CRON_STRING_PATH,
                    'path'
                )->setValue(
                    $cronExprString5
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
                throw new \Exception(__("We can\'t save the cron expression for Category."));
            }
        }

        return parent::afterSave();
    }
}