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
        $minutes = $this->getData('groups/autoSyncProduct/fields/minutes/value');
        $hours = $this->getData('groups/autoSyncProduct/fields/hours/value');
        $daysofmonth = $this->getData('groups/autoSyncProduct/fields/daysofmonth/value');
        $weekday = $this->getData('groups/autoSyncProduct/fields/weekdays/value');
        $time = $this->getData('groups/autoSyncProduct/fields/time/value');
        $time2 = $this->getData('groups/autoSyncProduct/fields/time2/value');
        $time3 = $this->getData('groups/autoSyncProduct/fields/time3/value');

        $frequencyEveryMinute = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_EVERY_MINUTE;
        $frequencyEveryHour = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_EVERY_HOUR;
        $frequencyDaily = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_DAILY;
        $frequencyWeekly = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_WEEKLY;
        $frequencyMonthly = \Qdos\QdosSync\Model\Config\Source\QdosSyncFrequency::CRON_MONTHLY;


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

            $minutes = (!empty($time[1])) ? $time[1] : '*';
            $hours = (!empty($time[0])) ? '*'.$time[0] : '*';
            $cronExprString = $minutes.' '.$hours.' * * *';
        }
        if($frequency == $frequencyMonthly){

            $minutes = (!empty($time[1])) ? $time[1] : '*';
            $hours = (!empty($time[0])) ? '*/'.$time[0] : '*';
            $cronExprString = $minutes.' '.$hours.' '.$daysofmonth.' * *';
        }
        if($frequency == $frequencyWeekly){
            $minutes = (!empty($time[1])) ? $time[1] : '*';
            $hours = (!empty($time[0])) ? '*/'.$time[0] : '*';
            $cronExprString = $minutes.' '.$hours.' * * '.$weekday;
        }

        // cron 2 by Swapnil Shinde
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
        if($frequency == $frequencyDaily){

            $minutes = (!empty($time1[1])) ? $time1[1] : '*';
            $hours = (!empty($time1[0])) ? '*/'.$time1[0] : '*';
            $cronExprString2 = $minutes.' '.$hours.' * * *';
        }

        $cronExprString2 = join(' ', array(join(',',$cronExprMinute2),join(',',$cronExprHour2),join(',',$cronExprDay2),'*',join(',',$cronExprWeek2)));

        // cron 3 by Swapnil Shinde
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
        if($frequency == $frequencyDaily){

            $minutes = (!empty($time2[1])) ? $time2[1] : '*';
            $hours = (!empty($time2[0])) ? '*/'.$time2[0] : '*';
            $cronExprString3 = $minutes.' '.$hours.' * * *';
        }

        $cronExprString3 = join(' ', array(join(',',$cronExprMinute3),join(',',$cronExprHour3),join(',',$cronExprDay3),'*',join(',',$cronExprWeek3)));
        /*Log code*/
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/productcronLogSwapnil.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        //echo $cronExprString;die();

        $logger->info('Cron schedules 1: '.$cronExprString.'2: '.$cronExprString2.' 3: '.$cronExprString3);

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
            throw new \Exception(__("We can\'t save the cron expression for Attribute."));
        }
        return parent::afterSave();
    }
}