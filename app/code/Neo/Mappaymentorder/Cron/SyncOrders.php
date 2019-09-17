<?php
namespace Neo\Mappaymentorder\Cron;

use \Psr\Log\LoggerInterface;

class SyncOrders {

    protected $logger;

    public function __construct(
        LoggerInterface $logger,
        \Neo\Mappaymentorder\Model\OrdersyncstatusFactory $ordersyncstatusFactory,
        \Magento\Sales\Model\Order $order,
        \Qdos\Sync\Model\Sync $syncModel
        /*\Qdos\OrderSync\Helper\Sales\Export $salesExport,
        \Neo\Mappaymentorder\Model\OrdersyncstatusFactory $ordersyncstatusFactory,
        \Magento\Sales\Model\Order $order,
        \Qdos\Sync\Model\Sync $syncModel*/
    ) {
        $this->logger = $logger;
        // $this->salesExport = $salesExport;
        $this->ordersyncstatusFactory = $ordersyncstatusFactory;
        $this->order = $order;
        $this->syncModel = $syncModel;
    }

  /**
    * Write to system.log
    * @return void
    */

    // public function execute() {
    //     $this->logger->info(__METHOD__);
    //     $start_time = date('Y-m-d H:i:s');
    //     $helper = $this->salesExport;
    //     $logModel = $this->syncModel;
    //     $logMsg = array();
    //     $logMsg[] = '<strong>NEO QDOS SYNC</strong>';
    //     $paymentMethod = $this->getRequest()->getParam('paymentmethod');
    //     $orderStatus = $this->getRequest()->getParam('orderstatus');
    //     if(!$paymentMethod && !$orderStatus) {
    //         $logMsg[] = 'Please try again.';
    //     } else {
    //         try {
    //             $orderArr = array();
    //             $orderIds = $this->ordersyncstatusFactory->create()->getCollection()
    //                         ->addFieldToSelect('order_id')
    //                         ->addFieldToFilter('status', 'notsynced')
    //                         ->addFieldToFilter('payment_method', $paymentMethod)
    //                         ->addFieldToFilter('order_status', $orderStatus)
    //                         ->load()
    //                         ->getData();
    //             $arrUpdateMappingStatus = array();
    //             foreach ($orderIds as $orderId){
    //                 $order = $this->order->load($orderId['order_id']);
    //                 if ($order->getId()){
    //                     $orderArr[] = $order;
    //                     $arrUpdateMappingStatus[] = $orderId['order_id'];
    //                 }else{
    //                     $result = $logModel::LOG_WARNING;
    //                     $logMsg[] = 'Order no longer exists.';
    //                 }
    //             }
    //             if (count($orderIds) > 0){
    //                 $logMsg[] = 'Sync Started ';
    //                 $result  = $helper->exportMultiOrders($orderArr,$logMsg);
    //                 $logMsg[] = 'Sync Executing ';
    //             }else{
    //                 $logMsg[] = 'No orders to sync';
    //             }
    //             if($result){
    //                 $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
    //                 $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
    //                 $connection = $resource->getConnection();

    //                 $updateIds = implode(',',$arrUpdateMappingStatus);
    //                 $query_update = "UPDATE order_sync_status SET status = 'synced', update_time = now() WHERE order_id in ($updateIds)";
    //                 $logMsg[] = "Mysql command: ".$query_update;
    //                 $connection->query($query_update);

    //                 $logMsg[] = 'Total of %d record(s) were successfully synced'. count($orderIds);
    //                 $logMsg[] = 'Status Updated ';
    //             }
    //         } catch (Exception $e) {
    //             $logMsg[] = $e->getMessage();
    //         }
    //         $logMsg[] = 'Sync Finished ';
    //     }

    //     if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
    //         $ipAddress = $_SERVER['REMOTE_ADDR'];
    //     }else{
    //         $ipAddress = '';
    //     }
    //     $this->syncModel->setActivityType('order')
    //             ->setStartTime($start_time)
    //             ->setEndTime(date('Y-m-d H:i:s'))
    //             ->setStatus($result)
    //             ->setDescription(implode('<br />', $logMsg))
    //             ->setIpAddress($ipAddress)
    //             ->save();
    //     $resultRedirect = $this->resultRedirectFactory->create();
    //     return $resultRedirect->setPath('*/*/index');
    // }

    public function execute() {
        $this->logger->info(__METHOD__);
    }
}