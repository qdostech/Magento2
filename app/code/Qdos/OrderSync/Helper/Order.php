<?php

namespace Qdos\OrderSync\Helper;

set_time_limit(0);
ini_set('max_execution_time', 30000);
ini_set('memory_limit', '2048M');
ini_set('default_socket_timeout', 2000);

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_logger;
	public function __construct(
          \Magento\Framework\App\Helper\Context $context,
          //\Psr\Log\LoggerInterface $logger,
          \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
          \Qdos\OrderSync\Helper\Sales\Export $exporthelperData,
          \Qdos\OrderSync\Model\Ordersync $orderSyncData,
          \Neo\Mappaymentorder\Model\Mappaymentorder $mappaymentorder,
          \Neo\Mappaymentorder\Model\Ordersyncstatus $ordersyncstatus,
          \Qdos\Sync\Model\Sync $syncData
          /* \Qdos\Sync\Helper\Sync $helperData,
           \Magento\Sales\Model\Order $orderData,
           \Qdos\QdosSync\Helper\Data $syncHelperData,
           \Magento\Framework\App\ResourceConnection $resource,
          // \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
           \Neo\Mappaymentorder\Model\Mappaymentorder $mappaymentorder*/
	) {

		parent::__construct($context);
       // $this->_logger = $logger; //$context->getLogger();
        $this->_logger = $context->getLogger();
        $this->exporthelperData = $exporthelperData;
        $this->directory_list = $directory_list; 
        $this->orderSyncData = $orderSyncData; 
        $this->mappaymentorder = $mappaymentorder;
        $this->ordersyncstatus = $ordersyncstatus;      
        $this->syncData = $syncData;
        /*$this->resource = $resource;
        $this->syncHelperData = $syncHelperData;
        $this->helperData = $helperData;	    
        $this->orderData = $orderData;        
        $this->mappaymentorder = $mappaymentorder;*/
        //$this->scopeConfig = $scopeConfig;
        
	}

    public function test(){
        echo "called test function from helper";
    }

    public function syncOrders(){
        $logFileName = "order_generation_".date('Ymd').'.log';
        $helper = $this->exporthelperData;
        $logMsg = array();
        $this->_logger->debug("in order sync ");
        $logMsg[] = '<strong>NEO QDOS SYNC</strong>';  

        try{
            $syncOrdersDayPrior = 5;
            if ($syncOrdersDayPrior != 0 || $syncOrdersDayPrior != '') {
                $todaysDate = date('Y-m-d H:i:s');
                $noOfDays = '-'.$syncOrdersDayPrior.' day';
                $strTodaysDate = strtotime($todaysDate);
                $convertStrTodaysDate = date("Y-m-d H:i:s", $strTodaysDate);
                $strcConvertStrTodaysDate = strtotime($convertStrTodaysDate . " ".$noOfDays);
                $fromDate = date('Y-m-d H:i:s', $strcConvertStrTodaysDate);

                $orderIds = $this->ordersyncstatus
                    ->getCollection()
                    ->addFieldToSelect('order_id')
                    ->addFieldToFilter('sync_status', 'no')
                    ->addFieldToFilter('created_time', array('from'=>$fromDate, 'to'=>$todaysDate))
                    ->load()
                    ->getData();                     
            }else{
                $orderIds = $this->ordersyncstatus
                    ->getCollection()
                    ->addFieldToSelect('order_id')
                    ->addFieldToFilter('sync_status', 'no')
                    ->load()
                    ->getData();
                    
            }             
            $orderArr = array();
            //$mapPaymentOrderData = Mage::getModel('mappaymentorder/mappaymentorder')->getCollection()->load()->getData();
            $mapPaymentOrderData = $this->mappaymentorder->getCollection()->load()->getData();
            $arrUpdateMappingStatus = array();  

            foreach ($orderIds as $orderId){
                $order = $this->orderData->create()->load($orderId['order_id']);
                $this->_logger->debug("orderId = ",$orderId['order_id']);
                //Mage::log("orderId = ".$orderId['order_id'], null, $logFileName,true);
                if ($order->getId()){
                    if ($this->checkOrderSyncingStatus($order, $mapPaymentOrderData)){
                        $orderArr[$order->getStoreId()][] = $order;
                        $arrUpdateMappingStatus[] = $orderId['order_id'];
                    }else{
                        $logMsg[] = "Skipped Order #{$order->getIncrementId()}.";
                    }
                }else{
                    $status = $logModel::LOG_WARNING;
                    $logMsg[] = 'Order no longer exists.';
                }
            }         

            if (count($arrUpdateMappingStatus) > 0){
                foreach ($orderArr as $storeId => $ordersData) {
                  $retResult  = $helper->exportMultiOrders($ordersData, $storeId);
                }

                $result = $retResult[0];
                //Mage::log('<pre>in order.php  = '.print_r($result),null,'order-custom.log',true);
                $this->_logger->debug("<pre>in order.php  = ",$result);
                $logMsg[] = implode('<br />', $retResult[1]);
                if (in_array('Error in processing', $retResult[1])) {
                    $status = $logModel::LOG_FAIL;
                    $this->helperData->sendMailForSyncFailed('Order');
                   // Mage::helper('sync/sync')->sendMailForSyncFailed('Order');
                }                
                if($result){
                    $message = $this->__("Synched successfully.");
                    //$resource   = Mage::getSingleton('core/resource');
                    $resource = $this->resource->create();
                    $write      = $resource->getConnection('core_write');
                    
                    $updateIds = implode(',',$arrUpdateMappingStatus);
                    $query_update     = "UPDATE ".$resource->getTableName('order_sync_status')." SET sync_status = 'yes', cc_cid = '', update_time = now() WHERE order_id in ($updateIds)";
                  
                    $write->query($query_update);   
                }else{
                    $message = $this->__("Synched error.");
                   
                }                
            }else{
                $logMsg[] = 'No Orders to Sync.';
            } 
        }catch(Exception $e){
                // $client->throwError($e->getMessage());
                $status = $logModel::LOG_FAIL;
                $this->_logger->debug("<pre>in order.php  = ",$result);
                //Mage::log("error ".$e->getMessage(), null, $logFileName,true);
                $this->helperData->sendMailForSyncFailed('Order');
                $logMsg[] = $e->getMessage();
        }
        /*-------WRITE LOG------*/
        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }else{
            $ipAddress = '';
        }  
        //Mage::getModel('sync/sync')->setActivityType('order')
        $this->syncData->setActivityType('order') 
                ->setStartTime($start_time)
                ->setEndTime(date('Y-m-d H:i:s', Mage::app()->getLocale()->storeTimeStamp()))
                ->setStatus($status)
                ->setDescription(implode('<br />', $logMsg))
                ->setIpAddress($ipAddress)
                ->save();

        return $arrUpdateMappingStatus;
    }// end of function sync customer

  /**
    * checkOrderCompletedStatus
    *   
    * @access   private
    * @params   object $order, array $mapPaymentOrderData
    * @return   array $orderSyncStatus
    **/
    //check the status of order if it is completed or not
    private function checkOrderSyncingStatus($order, $mapPaymentOrderData)
    {
        if ((!$order->getStatus()) || ($order->getStatus() == '')) {
            return false;
        }
        $logFileName = "order_generation_".date('Ymd').'.log';
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        $orderStatus = $order->getStatus();
        $arrOrderStatus = array();
        //Mage::log('Order Id - '.$order->getId().'  Status - '.$order->getStatus(), null, $logFileName);
        foreach ($mapPaymentOrderData as $key => $mappingDetails) {
            if ($mappingDetails['payment_method'] == $paymentMethod) {
                $arrOrderStatus = explode(',',$mappingDetails['order_status']);
            }
        }

        if (!in_array($orderStatus, $arrOrderStatus)) {
            return false;
        }
        return true;            
    } // end checkOrderSyncingStatus
   
   private function convertItemToArray($object){
        $new = array();
        if(is_object($object)){
            $new = array_change_key_case((array)$object, CASE_LOWER);
        }
        if(is_array($object)) {
            return $object;
        }
        return $new;
    }

    public function convertObjectsToArray($objs){
        $items = array();
        if(!is_array($objs))
            $items[] = $this->convertObjectToArray($objs);
        else
            foreach($objs as $obj){
                $items[] =  $this->convertObjectToArray($obj);
            }

        return $items;
    }

    public function convertObjectToArray($obj){
        $obj =  get_object_vars($obj);
        $result = array();
        foreach($obj as $key=>$value){
            $result[strtolower($key)] = $value;
        }
        return $result;
    }

     /**
     * @param $exeption_msg
     * @param $data
     * @return string
     */
    protected function decodeErrorMsg($exeption_msg, $data = null)
    {
        $errors = array(
            'url_key' => "url_key attribute already exists",
            'duplicate' => "UNQ_CATALOGINVENTORY_STOCK_ITEM_PRODUCT_ID_STOCK_ID",
            'configurable_1' => "UNQ_CH_CATALOG_PRODUCT_SUPER_LINK_PRODUCT_ID_PARENT_ID",
            'configurable_2' => "UNQ_CATALOG_PRODUCT_SUPER_LINK_PRODUCT_ID_PARENT_ID"
        );

        $tmp = "";
        foreach($errors as $key => $error){
            if (strpos($exeption_msg,$error) !== false) {
                $tmp = $key;
            }
        }

        switch ($tmp) {
            case 'url_key':
                return "Product with Url key ('".$data['url_key']."') already exists in magento db";
                break;
            case 'duplicate':
                return "Product with same SKU (".$data['sku'].") already exists in magento db. Please check ERP.";
                break;
            case 'configurable_1':
                $str = "Attributes not assigned to one or more child products, Please check ERP. Please check attributes for following SKU's of child products belonging to config product SKU - ".$data['sku'].": <br /> Child's SKU : <br />";
                $childs = explode(",", $data['associated']);
                if(is_array($childs)){
                    $i = 1;
                   foreach($childs as $child){
                       $str .= $i.". SKU - ".$child."<br />";
                       $i++;
                   }
                }
                return $str;
                break;
            case 'configurable_2':
                $str = "Attributes not assigned to one or more child products, Please check ERP. Please check attributes for following SKU's of child products belonging to config product SKU - ".$data['sku'].": <br /> Child's SKU : <br />";
                $childs = explode(",", $data['associated']);
                if(is_array($childs)){
                    $i = 1;
                   foreach($childs as $child){
                       $str .= $i.". SKU - ".$child."<br />";
                       $i++;
                   }
                }
                return $str;
                break;                
            default:
                return $exeption_msg;
        }

        return;
    } //end decodeErrorMsg 
    
}