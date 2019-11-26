<?php
namespace Qdos\OrderSync\Controller\Adminhtml\Ordersync;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;


class SyncOrders extends Action
{

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Qdos\OrderSync\Helper\Sales\Export $salesExport,
        \Qdos\Sync\Model\Sync $syncModel,
        \Neo\Mappaymentorder\Model\Mappaymentorder $mappaymentorderModel,
        \Neo\Mappaymentorder\Model\Ordersyncstatus $ordersyncstatusModel,
        \Magento\Sales\Model\Order $order,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->salesExport = $salesExport;
        $this->syncModel = $syncModel;
        $this->mappaymentorderModel = $mappaymentorderModel;
        $this->ordersyncstatusModel = $ordersyncstatusModel;
        $this->order = $order;
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $ordersyncIds = $this->getRequest()->getParam('data');
        $jsonDecoded = json_decode($ordersyncIds);

        $start_time = date('Y-m-d H:i:s');
        $helper = $this->salesExport;
        $logModel = $this->syncModel;
        $status = $logModel::LOG_SUCCESS;
        $logMsg = array();
        $result = array();
        $isOrder = false;
        $logMsg[] = '<strong>NEO QDOS SYNC</strong>';       

        try {
            $mapPaymentOrderData = $this->mappaymentorderModel->getCollection()->load()->getData();   

            foreach ($jsonDecoded as $key => $payorder) {
                $arrPayOrder = explode(',', $payorder);
                $paymentMethod = $arrPayOrder[0];
                $orderStatus = $arrPayOrder[1];
            
                $orderArr = array();
                $syncOrdersDayPrior = $this->_scopeConfig->getValue('payment_order_mapping/export_settings/sync_order_days_prior',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($syncOrdersDayPrior != 0 && $syncOrdersDayPrior != '') {
                    $todaysDate = date('Y-m-d H:i:s');
                    $noOfDays = '-'.$syncOrdersDayPrior.' day';
                    $strTodaysDate = strtotime($todaysDate);
                    $convertStrTodaysDate = date("Y-m-d H:i:s", $strTodaysDate);
                    $strcConvertStrTodaysDate = strtotime($convertStrTodaysDate . " ".$noOfDays);
                    $fromDate = date('Y-m-d H:i:s', $strcConvertStrTodaysDate);
                    $orderIds =  $this->ordersyncstatusModel->getCollection()
                                ->addFieldToSelect('order_id')
                                ->addFieldToFilter('sync_status', 'no')
                                //->addFieldToFilter('payment_method', $paymentMethod)
                                ->addFieldToFilter('created_time', array('from'=>$fromDate, 'to'=>$todaysDate));
                                //->load()
                                //->getData();                     
                }else{
                    
                    $orderIds =  $this->ordersyncstatusModel->getCollection()
                                ->addFieldToSelect('order_id')
                                ->addFieldToFilter('sync_status', 'no')
                                //->addFieldToFilter('payment_method', $paymentMethod)
                                //->addFieldToFilter('order_status', $orderStatus)
                                ->load()
                                ->getData(); 
                    
                }

                $arrUpdateMappingStatus = array();                                          
                foreach ($orderIds as $orderId){
                    $order = $this->order->load($orderId['order_id']);
                    if ($order->getId()){
                        if ($this->checkOrderSyncingStatus($order, $mapPaymentOrderData, $orderStatus)){
                            $orderArr[] = $order;
                            $arrUpdateMappingStatus[] = $orderId['order_id'];
                        }else{
                            $logMsg[] = "Skipped Order #{$order->getIncrementId()}.";
                        }   
                    }else{
                        $status = $logModel::LOG_PARTIAL;
                        $logMsg[] = 'Order no longer exists.';
                    }                   
                    
                }
                if (count($arrUpdateMappingStatus) > 0){
                    $retResult  = $helper->exportMultiOrders($orderArr);
                    $result = $retResult[0];
                    $logMsg[] = implode('<br />', $retResult[1]);
                }else{
                    $messageText = 'No orders to sync for "'.$paymentMethod.'" Payment Method and "'.$orderStatus.'" Order Status';
                    $logMsg[] = $messageText;
                }
                if($result){
                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                    $connection = $resource->getConnection();
                    $tableName = $resource->getTableName('order_sync_status');
                    
                    $updateIds = implode(',',$arrUpdateMappingStatus);
                    $query_update     = "UPDATE ".$tableName." SET sync_status = 'yes', cc_cid = '', update_time = now() WHERE order_id in ($updateIds)";
                    $connection->query($query_update);   

                    $messageText = 'Total of '.count($arrUpdateMappingStatus).' record(s) were successfully synced for "'.$paymentMethod.'" Payment Method and "'.$orderStatus.'" Order Status';
                    
                    $logMsg[] = $messageText;
                    $isOrder = true;
                }
                $result = false;
            }
        } catch (Exception $e) {
            $logMsg[] = 'Error in processing';
            $logMsg[] = $e->getMessage();
        }

        if ($isOrder == false) {
            $logMsg[] = 'No Orders to Sync.';
        }
        if (in_array('Error in processing', $logMsg)) {
            $status = $logModel::LOG_FAIL;
            // Mage::helper('sync/sync')->sendMailForSyncFailed('Order');
        }        

        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }else{
            $ipAddress = '';
        }         
        $this->syncModel->setActivityType('order')
                ->setStartTime($start_time)
                ->setEndTime(date('Y-m-d H:i:s'))
                ->setStatus($status)
                ->setDescription(implode('<br />', $logMsg))
                ->setIpAddress($ipAddress)
                ->save();        
        $this->_redirect('*/*/index'); 
    }

    //check the status of order if it is completed or not
    private function checkOrderSyncingStatus($order, $mapPaymentOrderData, $orderStatusCheck)
    {
        if ((!$order->getStatus()) || ($order->getStatus() == '')) {
            return false;
        }

        $orderStatus = $order->getStatus();
        if ($orderStatus != $orderStatusCheck) {
           return false;
        }

        return true;            
    }
}