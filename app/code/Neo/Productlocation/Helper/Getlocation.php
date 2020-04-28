<?php

namespace Neo\Productlocation\Helper;

class Getlocation extends \Magento\Framework\App\Helper\AbstractHelper
{

	public function __construct(
          \Magento\Framework\App\Helper\Context $context,
          \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
          \Qdos\Sync\Model\Sync $syncModel,
          \Neo\Productlocation\Model\Productlocation $productlocation

	){

        parent::__construct($context);
        $this->directory_list = $directory_list;
        $this->syncModel = $syncModel;
        $this->productlocation = $productlocation;
	}

  //functions to sync Get Location of Products
  public function syncGetLocation($storeId = 0){
    try {
        ini_set("soap.wsdl_cache_enabled", 0);
        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base.'/Connection.php'; 
        require_once($lib_file);
        $client = Test();
        $message = 'success';
        $logModel = $this->syncModel;
        $_result = $logModel::LOG_SUCCESS;
        $start_time = date('Y-m-d H:i:s');
        $logMsgs = $logMsg = $productLogIds = $hiddenProductArr = array();
        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }else{
            $ipAddress = '';
        }
        $logModel->setActivityType('get_location')
                 ->setStartTime($start_time)
                 ->setStatus($logModel::LOG_PENDING)
                 ->setIpAddress($ipAddress)
                 ->save(); 
        $logFileName = "get_location_".date('Ymd').'.log';
        $clientnew = $client->getConnect();
    
       $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $unset = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/import_product_settings/not_sync_attribute_properties', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $unsetProperties = array();

         if (strlen($unset)){
            $unsetProperties = explode(',',$unset);
        }
    
        $resultClient =  $clientnew->__call('Getlocation', array(array('store_url'=>$store_url)));//->GetLocation(array('store_url'=> $store_url));
//var_dump($resultClient);die();
        $objCollection = array();

        if ($resultClient->outErrorMsg && strlen($resultClient->outErrorMsg) > 0) {
           echo  $logMsgs[] = 'SOAP LOGIN ERROR: ' . $resultClient->outErrorMsg; exit;
        }else{
            $result = $resultClient->GetLocationResult;
            if (is_object($result) && isset($result->Location)) {
                $objCollection = $result->Location;
               
        }


   
// if(is_array($objCollection)||is_object($objCollection)):

        foreach($objCollection as $key=>$value){
            $data = array();
            $data[$value->LOCATION_ID]['location_id'] = $value->LOCATION_ID;
            $data[$value->LOCATION_ID]['location_name'] = $value->NAME;
            
            $existing_location = $this->productlocation->getCollection()
                                    ->addFieldToFilter('location_id',array('eq' => $value->LOCATION_ID))->getFirstItem();
            $logMsg[] = 'Location Id = '.$value->LOCATION_ID;
            $existing_location = $existing_location->getData();
            print_r($existing_location);
            if ($existing_location) {
                $model = $this->productlocation->addData($data[$value->LOCATION_ID])
                    ->setId($existing_location['id'])
                    ->save();
            }else{
                $model = $this->productlocation->addData($data[$value->LOCATION_ID])
                ->save();
            }
        }
        $message = 'success';

            }

        // else:
        //     $logMsg[]="location data are empty";
        // endif;

        $logModel->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus($_result)
            ->setDescription(implode('<br />', $logMsg))
            ->save();
    }catch(Exception $e){
      $logMsgs[] = 'Error in processing'."Sync Get Location failed due to following reasons - ".$e->getMessage();
      $message = $e->getMessage();
    }
    return $message;
  }

   

}