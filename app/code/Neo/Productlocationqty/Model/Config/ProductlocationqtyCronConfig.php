<?php

/**
 * @author Pradeep Sanku
 */

namespace Neo\Productlocationqty\Model\Config;
class ProductlocationqtyCronConfig extends \Magento\Framework\App\Config\Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/cron_product_location_qty_sync/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/default/jobs/cron_product_location_qty_sync/run/model';

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
        $frequency = $this->getData('groups/autoSyncProductLocationQty/fields/frequency/value');
        $time = $this->getData('groups/autoSyncProductLocationQty/fields/time/value');
       // $productlocationqtySync=$this->getData('groups/autoSyncProductLocationQty/fields/add_new_sync_productlocationqty_schedule/value');

          $time1= $this->getData('groups/autoSyncProductLocationQty/fields/time2/value');
          $productlocationqtySync2=$this->getData('groups/autoSyncProductLocationQty/fields/add_new_sync_productlocationqty_schedule/value');

          $time2= $this->getData('groups/autoSyncProductLocationQty/fields/time3/value');
          $productlocationqtySync3=$this->getData('groups/autoSyncProductLocationQty/fields/add_another_new_sync_productlocationqty_schedule/value');

          $time3= $this->getData('groups/autoSyncProductLocationQty/fields/time4/value');
          $productlocationqtySync4=$this->getData('groups/autoSyncProductLocationQty/fields/add_another_new_one_sync_productlocationqty_schedule/value');
           $time4= $this->getData('groups/autoSyncProductLocationQty/fields/time4/value');
          $productlocationqtySync5=$this->getData('groups/autoSyncProductLocationQty/fields/add_another_new_two_sync_productlocationqty_schedule/value');




        $daysofmonth = $this->getData('groups/autoSyncProductLocationQty/fields/daysofmonth/value');
        $weekday = $this->getData('groups/autoSyncProductLocationQty/fields/weekdays/value');

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
          /////////////////////////////////////////////////////////////////////
       $cronExprMinute1 = $cronExprHour1 = $cronExprDay1 = $cronExprWeek1 = array();
        if (!in_array(intval($time1['1']),$cronExprMinute1)){
            $cronExprMinute1[] = ($frequency == $frequencyEveryMinute) ? '*/'.intval($time1['1']) : intval($time1['1']);
        }

        if ($frequency == $frequencyEveryMinute){
            $hour1 = '*';
        }else{
            $hour1 = ($frequency == $frequencyEveryHour) ? '*/'.intval($time1['0']) : intval($time1['0']);
        }

        if (!in_array($hour1, $cronExprHour1)){
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


        ##############################
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

        ##############################****############
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

        ######################**************##########

        if ($frequency==$frequencyDaily)

        {

            $cr1f=explode(" ", $cronExprString);
            $cr2f=explode(" ", $cronExprString1);
            $cr3f=explode(" ", $cronExprString2);
            $cr4f=explode(" ", $cronExprString3);
            $cr5f=explode(" ", $cronExprString4);
            $f1="";
            for ($i=0;$i<=4;$i++)
            {

                    $f1 .= $cr1f[$i].(($cronExprString1) ? ",". $cr2f[$i]:"")."".(($cronExprString2)?",".$cr3f[$i]:"")."".(($cronExprString3)?",".$cr4f[$i]:"")."".(($cronExprString4)?",".$cr5f[$i]:"").""."-";
                
            }
            // print_r((explode("-", $f1)))
            $f1=explode("-", $f1);
            $cronExprString=$cronExprString1=$cronExprString2=$cronExprString3=$cronExprString4= implode(',', array_unique((explode(",", $f1[0]))))." ".implode(',', array_unique((explode(",", $f1[1]))))." ".implode(',', array_unique((explode(",", $f1[2]))))." ".implode(',', array_unique((explode(",", $f1[3]))))." ".implode(',', array_unique((explode(",", $f1[4]))));
        }
        /////////////////////////////////////////////////////////////////////






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



        if($productlocationqtySync2){
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
        if($productlocationqtySync3){
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


        if($productlocationqtySync4){
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
        }
        if($productlocationqtySync5){
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
                throw new \Exception(__("We can\'t save the cron expression for Attribute."));
            }
        }

        return parent::afterSave();
    }
}