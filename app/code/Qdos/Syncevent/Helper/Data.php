<?php
/**
 * Copyright © 2015 Qdos . All rights reserved.
 */
namespace Qdos\Syncevent\Helper;
set_time_limit(0);
ini_set('max_execution_time', 100000);
ini_set('memory_limit', '2048M');
ini_set('default_socket_timeout', 2000);
ini_set('display_errors','On');
if(!extension_loaded("soap")){
  dl("php_soap.dll");
}
ini_set("soap.wsdl_cache_enabled","0");

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
// use \Psr\Log\LoggerInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

	/**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    // protected $_logger;


	/**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	public function __construct(\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        PageFactory $resultPageFactory,
        \Qdos\QdosSync\Model\Log $log,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $time
	) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
        $this->directory_list = $directory_list;
        $this->_log = $log;
        $this->date = $date;
        $this->time = $time;
        // $this->_logger = $context->$logger;
	}

  public function importEvents(){
    // $this->_logger->info(__METHOD__);
		$base = $this->directory_list->getPath('lib_internal');
    $lib_file = $base.'/Connection.php'; 
    require_once($lib_file);
   $clientLog= $client = Test();
    $resultClient = $client->connect();

    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $logFileName = "syncEvents-".date('Ymd').".log";
    $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');

    $client = $resultClient->GetEvents(array('STORE_URL' => $store_url));
    $logFileName = "syncEvents-".date('Ymd').".log";
    $error = false;
    $start_time = date('Y-m-d H:i:s');
    $logMsg = array();
    $clientLog->setLog("Sync Events Started. ",null,$logFileName); 
    /*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/EventLogSwapnil.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);*/

    try {
      if ($client->outErrorMsg && strlen($client->outErrorMsg) > 0){
        $error = true;
        $logMsg[] = (string)$client->outErrorMsg;
        //$logger->info('in if : '.(string)$client->outErrorMsg);
        //$clientLog->setLog((string)$client->outErrorMsg,null,$logFileName);
      }else{
        $result = $client->GetEventsResult;
       // $logger->info('In else : '.json_encode($result));
        $i = 0;
        if (is_object($result) && isset($result->Event)) {
          $eventList = $this->convertObjToArray($result->Event);
          // $clientLog->setLog($eventList,null,$logFileName); 
          $logMsg[]="Total Records ". count($eventList) ."";
           $clientLog->setLog('Received: ' . count($eventList) . ' event(s).',null,$logFileName);
          foreach ($eventList as $event) {
            if (isset($event['event_id']) and is_numeric($event['event_id'])) {
              $_events = $objectManager->create('\Qdos\Syncevent\Model\Syncevent');
              $_events_load = $_events->load($event['event_id']);
              if (empty($_event)) {
                $event['created_time'] = $this->date->gmtDate();
              }

              // $location = Mage::getModel('event/cat')->loadByAttribute('title', $event['location']);
              // if (!empty($location) && $location->getId()) {
              //   $event['cat_id'] = $location->getId();
              // } else {
              //   $event['cat_id'] = Mage::helper('qdossync/event_location')->addNewLocation($event);
              // }

              $event['updated_time'] = $this->date->gmtDate();
              $startTime = date('Y-m-d H:i:s', strtotime($event['start_dt']));
              $endTime = date('Y-m-d H:i:s', strtotime($event['end_dt']));
              $approverdate = date('Y-m-d H:i:s', strtotime($event['approved_dt']));
              $reminderdate = date('Y-m-d H:i:s', strtotime($event['reminder_dt']));
              $req_confirm_dt = date('Y-m-d H:i:s', strtotime($event['req_confirm_dt']));
              $checkin_date = date('Y-m-d H:i:s', strtotime($event['checkin_date']));
              $checkout_date = date('Y-m-d H:i:s', strtotime($event['checkout_date']));
              
              $event['title'] = $event['event_name'];
              if (!$_events_load->getId()){
                $qdosId = $event['event_id'];
              }
              //$clientLog->setLog('--event id --'.$event['event_id'].'--event load id --'.$_events_load->getId(),null,$logFileName);

              if($_events_load->getId() == null) {
                //$clientLog->setLog('in if --event id --'.$event['event_id'].'--event load id --'.$_events_load->getId(),null,$logFileName);
                $_events->setData($event);
                $_events->save();
                $i++;
                $newId = $_events->getId();
                // $clientLog->setLog('--new id ---'.$newId,null,$logFileName);
                //echo "-=- New Id --".$newId;

                $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
                $connection= $this->_resources->getConnection();
                $Table = $this->_resources->getTableName('syncevent_syncevent');
                // Update query
                $write = "UPDATE `".$Table."` SET `id`= ". $event['event_id']." WHERE `id`= ".$newId;
                $connection->query($write);
              }else{
                // $clientLog->setLog('Updated events',null,$logFileName);
                isset($event['event_name']) ? $_events_load->setEventname(trim($event['event_name'])) : $_events_load->setEventname('');
                isset($event['objective']) ? $_events_load->setObjective(trim($event['objective'])) : $_events_load->setObjective('');
                isset($event['event_desc']) ? $_events_load->setEventDesc(trim($event['event_desc'])) : $_events_load->setEventDesc('');
                isset($event['start_dt']) ? $_events_load->setStartDt(date('Y-m-d H:i:s', strtotime($event['start_dt']))) : $_events_load->setStartDt('');
                isset($event['end_dt']) ? $_events_load->setEndDt(date('Y-m-d H:i:s', strtotime($event['end_dt']))) : $_events_load->setEndDt('');
                isset($event['channel']) ? $_events_load->setChannel(trim($event['channel'])) : $_events_load->setChannel('');
                isset($event['type']) ? $_events_load->setType(trim($event['type'])) : $_events_load->setType('');
                isset($event['status']) ? $_events_load->setStatus(trim($event['status'])) : $_events_load->setStatus('');
                isset($event['cost']) ? $_events_load->setCost($event['cost']) : $_events_load->setCost('');
                isset($event['approver']) ? $_events_load->setApprover(trim($event['approver'])) : $_events_load->setApprover('');
                isset($event['approved_dt']) ? $_events_load->setApprovedDt(date('Y-m-d H:i:s', strtotime($event['approved_dt']))) : $_events_load->setApprovedDt('');
                isset($event['reminder_flg']) ? $_events_load->setReminderFlg(trim($event['reminder_flg'])) : $_events_load->setReminderFlg('');
                isset($event['reminder_dt']) ? $_events_load->setReminderDt(date('Y-m-d H:i:s', strtotime($event['reminder_dt']))) : $_events_load->setReminderDt('');
                isset($event['req_confirm_dt']) ? $_events_load->setReqConfirmDt(date('Y-m-d H:i:s', strtotime($event['req_confirm_dt']))) : $_events_load->setReqConfirmDt('');
                isset($event['location']) ? $_events_load->setLocation(trim($event['location'])) : $_events_load->setLocation('');
                isset($event['cancel_allowed']) ? $_events_load->setCancelAllowed($event['cancel_allowed']) : $_events_load->setCancelAllowed('');
                isset($event['max_capacity']) ? $_events_load->setMaxCapacity($event['max_capacity']) : $_events_load->setMaxCapacity('');
                isset($event['waitlist_allowed']) ? $_events_load->setWaitlistAllowed(trim($event['waitlist_allowed'])) : $_events_load->setWaitlistAllowed('');
                isset($event['waitlist_number']) ? $_events_load->setWaitlistNumber($event['waitlist_number']) : $_events_load->setWaitlistNumber('');
                isset($event['deposit_req']) ? $_events_load->setDepositReq(trim($event['deposit_req'])) : $_events_load->setDepositReq('');
                isset($event['on_day_register']) ? $_events_load->setOnDayRegister(trim($event['on_day_register'])) : $_events_load->setOnDayRegister('');
                isset($event['position_id']) ? $_events_load->setPositionId($event['position_id']) : $_events_load->setPositionId('');
                isset($event['event_owner_id']) ? $_events_load->setEventOwnerId($event['event_owner_id']) : $_events_load->setEventOwnerId('');
                isset($event['campaign_id']) ? $_events_load->setCampaignId($event['campaign_id']) : $_events_load->setCampaignId('');
                isset($event['event_ref']) ? $_events_load->setEventRef($event['event_ref']) : $_events_load->setEventRef('');
                isset($event['product_id']) ? $_events_load->setProductId($event['product_id']) : $_events_load->setProductId('');
                isset($event['parent_id']) ? $_events_load->setParentId($event['parent_id']) : $_events_load->setParentId('');
                isset($event['annual_event']) ? $_events_load->setAnnualEvent(trim($event['annual_event'])) : $_events_load->setAnnualEvent('');
                isset($event['loc_id']) ? $_events_load->setLocId($event['loc_id']) : $_events_load->setLocId('');
                isset($event['contact_id']) ? $_events_load->setContactId($event['contact_id']) : $_events_load->setContactId('');
                isset($event['first_account_id']) ? $_events_load->setFirstAccountId($event['first_account_id']) : $_events_load->setFirstAccountId('');
                isset($event['second_account_id']) ? $_events_load->setSecondAccountId($event['second_account_id']) : $_events_load->setSecondAccountId('');
                isset($event['acnt_rel_type']) ? $_events_load->setAcntRelType(trim($event['acnt_rel_type'])) : $_events_load->setAcntRelType('');
                isset($event['venue']) ? $_events_load->setVenue(trim($event['venue'])) : $_events_load->setVenue('');
                isset($event['assess_templ_id']) ? $_events_load->setAssessTemplId($event['assess_templ_id']) : $_events_load->setAssessTemplId('');
                isset($event['dates_tbc']) ? $_events_load->setDatesTbc(trim($event['dates_tbc'])) : $_events_load->setDatesTbc('');
                isset($event['event_short_name']) ? $_events_load->setEventShortName(trim($event['event_short_name'])) : $_events_load->setEventShortName('');
                isset($event['html_short']) ? $_events_load->setHtmlShort(trim($event['html_short'])) : $_events_load->setHtmlShort('');
                isset($event['html_long']) ? $_events_load->setHtmlLong(trim($event['html_long'])) : $_events_load->setHtmlLong('');
                isset($event['checkin_date']) ? $_events_load->setCheckinDate(date('Y-m-d H:i:s', strtotime($event['checkin_date']))) : $_events_load->setCheckinDate('');
                isset($event['checkout_date']) ? $_events_load->setCheckoutDate(date('Y-m-d H:i:s', strtotime($event['checkout_date']))) : $_events_load->setCheckoutDate('');
                isset($event['location_name']) ? $_events_load->setLocationName(trim($event['location_name'])) : $_events_load->setLocationName('');
                isset($event['colour']) ? $_events_load->setColour(trim($event['colour'])) : $_events_load->setColour('');
                isset($event['sku']) ? $_events_load->setSku($event['sku']) : $_events_load->setSku('');
                $_events_load->save(); 
              }
            }
          } //foreach
          $this->reindexdata();
          $clientLog->setLog('Sync success: ' . $i . ' event(s)',null,$logFileName); 
           $logMsg[]='Sync success: ' . $i . ' event(s)';
        }
      }
    }catch(Exception $e){
      $error = true;
      $clientLog->setLog('Import events failed.' . $ex->getMessage(),null,$logFileName); 
    }

    if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
      $ipAddress = $_SERVER['REMOTE_ADDR'];
    }else{
      $ipAddress = '';
    }

    $logModel = $objectManager->get('\Qdos\Sync\Model\Sync');
    $logModel->setActivityType('event')
              ->setStartTime($start_time)
              ->setEndTime(date('Y-m-d H:i:s'))
              ->setStatus(!$error)
              ->setDescription(implode('<br />', $logMsg))
              ->setIpAddress($ipAddress)
              ->save();
              return (!$error);

  }
	
  public function convertObjToArray($object){
    $new = array();
    if(is_object($object)){
      $new[] = array_change_key_case((array)$object, CASE_LOWER);
    }
    if(is_array($object)) {
      foreach ($object as $obj){
        if (is_object($obj)){
          $new[] = array_change_key_case((array)$obj, CASE_LOWER);
        }
      }
    }
    return $new;
  }

  public function reindexdata()
  {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $Indexer = $objectManager->create('Magento\Indexer\Model\Processor');
      $Indexer->reindexAll();
  }
}