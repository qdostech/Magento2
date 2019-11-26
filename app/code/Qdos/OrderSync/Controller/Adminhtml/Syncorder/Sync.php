<?php
namespace Qdos\OrderSync\Controller\Adminhtml\Syncorder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;


class Sync extends Action
{


    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Qdos\OrderSync\Helper\Sales\Export $salesExport,
        \Qdos\Sync\Model\Sync $syncModel,
        \Neo\Mappaymentorder\Model\Mappaymentorder $mappaymentorderModel,
        \Magento\Sales\Model\Order $order,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->directory_list = $directory_list;
        $this->salesExport = $salesExport;
        $this->syncModel = $syncModel;
        $this->mappaymentorderModel = $mappaymentorderModel;
        $this->order = $order;

    }

    public function execute()
    {
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

            //return $arrUpdateMappingStatus;
            if (count($arrUpdateMappingStatus) > 0){
                $retResult  = $helper->exportMultiOrders($orderArr);
                $result = $retResult[0];
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
                    $message = count($orderArr)." Order(s) Synched successfully.";
                    $this->messageManager->addSuccess($message);
                    $logMsg[] = $message;
                }else{
                    $message = $this->__("Synched error.");
                    $this->messageManager->addError($message);
                    $logMsg[] = 'Synched error.';
                }
            }else{
                $message = 'No Orders to Sync.';
                $this->messageManager->addNotice($message);
                $logMsg[] = $message;
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