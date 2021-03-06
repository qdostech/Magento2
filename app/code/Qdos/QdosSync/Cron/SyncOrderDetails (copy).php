<?php
/**
 *	@author Pradeep Sanku
 */
namespace Qdos\QdosSync\Cron;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use \Psr\Log\LoggerInterface;

class SyncOrderDetails extends Action
{
    protected $_logger;
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Qdos\OrderSync\Helper\Sales\Export $salesExport,
        \Qdos\Sync\Model\Sync $syncModel,
        \Neo\Mappaymentorder\Model\Mappaymentorder $mappaymentorderModel,
        \Magento\Sales\Model\Order $order,
        array $data = [])
    {
         parent::__construct($context);
        $this->_logger = $logger;
        $this->directory_list = $directory_list;
        $this->salesExport = $salesExport;
        $this->syncModel = $syncModel;
        $this->mappaymentorderModel = $mappaymentorderModel;
        $this->order = $order;
    }

    public function execute(){
    	        /*Log code*/
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/SyncOrderDetails.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
		$start_time = date('Y-m-d H:i:s');
        $logFileName = "order_generation_".date('Ymd').'.log';
        $helper =  $this->salesExport;
        //$arrDetails = $this->checkOrderSyncStatus();
        $logModel = $this->syncModel;
        $logMsg = array();
        $status = $logModel::LOG_SUCCESS;
        $logMsg[] = '<strong>NEO QDOS SYNC</strong>';   
        try {
         
            $orderArr = array();
            $mapPaymentOrderData = $this->mappaymentorderModel->getCollection()->load()->getData();
            $arrUpdateMappingStatus = array();          

            $order =$this->order->load($this->getRequest()->getParam('order_id'));
            if ($order->getId()){
                if ($this->checkOrderSyncingStatus($order, $mapPaymentOrderData)){
                    $orderArr[] = $order;
                    $arrUpdateMappingStatus[] = $this->getRequest()->getParam('order_id');
                }else{
                    $logMsg[] = "Skipped Order #{$order->getIncrementId()}.";
                }
            }else{
                $status = $logModel::LOG_WARNING;
                $logMsg[] = 'Order no longer exists.';
            }
            $logger->info('Order no: '.$this->getRequest()->getParam('order_id').'Order : '.json_encode($order));
            //Mage::log('Order Array '.print_r($orderArr, true), null, 'qdos-sync-order-' . date('Ymd') . '.log');
            //return $arrUpdateMappingStatus;
            if (count($arrUpdateMappingStatus) > 0){
                $retResult  = $helper->exportMultiOrders($orderArr);
                $result = $retResult[0];
                $logger->info('result : '.json_encode($result));
                //Mage::log("error ".print_r($retResult, 1), null, 'testing.log');
                // $logMsg[] = implode('<br />', $retResult[1]);
                // if (in_array('Error in processing', $retResult[1])) {
                //     $status = $logModel::LOG_FAIL;
                //     // Mage::helper('sync/sync')->sendMailForSyncFailed('Order');
                // }     
                if($result){
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                    $connection = $resource->getConnection();
                    $tableName = $resource->getTableName('order_sync_status');
                    
                    $updateIds = implode(',',$arrUpdateMappingStatus);
                    $query_update     = "UPDATE ".$tableName." SET sync_status = 'yes', cc_cid = '', update_time = now() WHERE order_id in ($updateIds)";
                    $connection->query($query_update);   
                    // $this->_getSession()->addSuccess($message);
                    $message = count($orderArr)." Order(s) Synched successfully.";
                    $logMsg[] = $message;
                }else{
                    // $message = $this->__("Synched error.");
                    // $this->_getSession()->addError($message);
                    $logMsg[] = 'Synched error.';
                   
                }
                //Mage::dispatchEvent('qdossync_manual_sync_order_success', array('orders' => $orderArr));
            }else{
                // $this->_getSession()->addError($message);
                $logMsg[] = 'No Orders to Sync.';
            } 
        } catch (Exception $e) {
            $status = $logModel::LOG_FAIL;
            // Mage::helper('sync/sync')->sendMailForSyncFailed('Order');
            $logMsg[] = $e->getMessage();
           // Mage::log("error ".$e->getMessage(), null, $logFileName);
           // $this->_getSession()->addError($e->getMessage());
            
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
         // $this->_redirect('*/*/index');  
         $resultRedirect = $this->resultRedirectFactory->create();
         $resultRedirect->setPath('ordersync/syncorder/index');
         return $resultRedirect;
        $this->_logger->debug('Cron Works in Delete Logs');
        return $this;
    }

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
        // Mage::log('Order Id - '.$order->getId().'  Status - '.$order->getStatus(), null, $logFileName);
        foreach ($mapPaymentOrderData as $key => $mappingDetails) {
            if ($mappingDetails['payment_method'] == $paymentMethod) {
                $arrOrderStatus = explode(',',$mappingDetails['order_status']);
            }
        }

        if (!in_array($orderStatus, $arrOrderStatus)) {
            return false;
        }
        return true;            
    }
}