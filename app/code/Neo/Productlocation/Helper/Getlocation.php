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
        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base.'/Test.php'; 
        require_once($lib_file);
        $client = Test();

        $clientnew = $client->connect();
        $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $resultClient =  $clientnew->Getlocation(array('store_url'=> $store_url ));
        $objCollection = array();
        if ($resultClient->outErrorMsg && strlen($resultClient->outErrorMsg) > 0) {
            $logMsgs[] = 'SOAP LOGIN ERROR: ' . $resultClient->outErrorMsg;
        }else{
            $result = $resultClient->GetLocationResult;
            if (is_object($result) && isset($result->Location)) {
                $objCollection = $result->Location;
        }

        foreach($objCollection as $key=>$value){
            $data = array();
            $data[$value->LOCATION_ID]['location_id'] = $value->LOCATION_ID;
            $data[$value->LOCATION_ID]['location_name'] = $value->NAME;
            
            $existing_location = $this->productlocation->getCollection()
                                    ->addFieldToFilter('location_id',array('eq' => $value->LOCATION_ID));
            $logMsg[] = 'Product Id = '.$value->PRODUCT_ID;
            $existing_location = $existing_location->getData();
            if ($existing_location[0]['id']) {
                $model = $this->productlocation->addData($data[$value->LOCATION_ID])
                    ->setId($existing_location[0]['id'])
                    ->save();
            }else{
                $model = $this->productlocation->addData($data[$value->LOCATION_ID])
                ->save();
            }
        }
        $message = 'success';
            }
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