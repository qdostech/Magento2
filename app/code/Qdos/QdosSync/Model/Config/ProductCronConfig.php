<?php

/**
 * @author Pradeep Sanku
 */

namespace Qdos\QdosSync\Model\Config;
class ProductCronConfig extends \Magento\Framework\App\Config\Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/cron_product_sync/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/default/jobs/cron_product_sync/run/model';

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

    public function afterSave()
    {
        $frequency = $this->getData('groups/autoSyncProduct/fields/frequency/value');
        // $minutes = $this->getData('groups/autoSyncProduct/fields/minutes/value');
        // $hours = $this->getData('groups/autoSyncProduct/fields/hours/value');
        $daysofmonth = $this->getData('groups/autoSyncProduct/fields/daysofmonth/value');
        $weekday = $this->getData('groups/autoSyncProduct/fields/weekdays/value');
        $productSync1=$this->getData('groups/autoSyncProduct/fields/auto_sync_product/value');
        $time = $this->getData('groups/autoSyncProduct/fields/time/value');
        $productSync2=$this->getData('groups/autoSyncProduct/fields/add_new_sync_product_schedule/value');
        $time1 = $this->getData('groups/autoSyncProduct/fields/time1/value');
        $productSync3=$this->getData('groups/autoSyncProduct/fields/add_another_new_sync_product_schedule/value');
        $time2 = $this->getData('groups/autoSyncProduct/fields/time2/value');
        $frequencyEveryMinute = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_EVERY_MINUTE;
        $frequencyEveryHour = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_EVERY_HOUR;
        $frequencyDaily = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_DAILY;
        $frequencyWeekly = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_WEEKLY;
        $frequencyMonthly = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_MONTHLY;


        // if(!empty($hours)) {
        //     $hours = implode(",", $hours);
        // }
        // if(!empty($minutes)) {
        //     $minutes = implode(",", $minutes);
        // }
        // if(!empty($weekday)) {
        //     $weekday = implode(",", $weekday);
        // }
        // if(!empty($daysofmonth)) {
        //     $daysofmonth = implode(",", $daysofmonth);
        // }


        // $cronExprString ='';
        // if($frequency == $frequencyEveryMinute){
        //     $minutes = (!empty($time[1])) ? '*/'.$time[1] : '*';
        //     $cronExprString = $minutes.' * * * *';
        // }
        // if($frequency == $frequencyEveryHour){
        //     $minutes = (!empty($time[1])) ? $time[1] : '*';
        //     $hours = (!empty($time[0])) ? '*/'.$time[0] : '*';
        //     $cronExprString = $minutes.' '.$hours.' * * *';
        // }
        // if($frequency == $frequencyDaily){
        //     $cronExprString = $minutes.' '.$hours.' * * *';
        // }
        // if($frequency == $frequencyMonthly){
        //     $cronExprString = $minutes.' '.$hours.' '.$daysofmonth.' * *';
        // }
        // if($frequency == $frequencyWeekly){
        //     $cronExprString = $minutes.' '.$hours.' * * '.$weekday;
        // }
//Code by Swapnil
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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/syncProductSwapnil.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->info('in syncProduct : '.$cronExprString." & ".$cronExprString1." & ".$cronExprString2);
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
            throw new \Exception(__("We can\'t save the cron expression Product."));
        }
        
        if($productSync2){
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
                throw new \Exception(__("We can\'t save the cron expression Product."));
            }
        }

        if($productSync3){
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
                throw new \Exception(__("We can\'t save the cron expression Product."));
            }
        }
        return parent::afterSave();
    }
}