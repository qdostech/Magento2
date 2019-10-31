<?php
/**
 * @author Pradeep Sanku
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
        \Neo\Mappaymentorder\Model\OrdersyncstatusFactory $ordersyncstatusFactory,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->_logger = $logger;
        $this->directory_list = $directory_list;
        $this->salesExport = $salesExport;
        $this->syncModel = $syncModel;
        $this->mappaymentorderModel = $mappaymentorderModel;
        $this->order = $order;
        $this->ordersyncstatusFactory = $ordersyncstatusFactory;
    }

    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/RaviSyncOrderDetails.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('call yes');

        $this->_logger->info(__METHOD__);
        $start_time = date('Y-m-d H:i:s');
        $helper = $this->salesExport;
        $logModel = $this->syncModel;
        $logMsg = array();
        $status = $logModel::LOG_SUCCESS;
        $logMsg[] = '<strong>NEO QDOS SYNC</strong>';
        $paymentMethod = $this->getRequest()->getParam('paymentmethod');
        $orderStatus = $this->getRequest()->getParam('orderstatus');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $syncOrderDetails = $this->_scopeConfig->getValue('qdosSync/autoSyncOrderDetails/auto_sync_order_details',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($syncOrderDetails){
            try {
                $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                if (strtolower($cronStatus) == 'running') {
                    $logMsg = 'Another Sync already in progress. Please wait...';
                    $this->_logger->info($logMsg);
                }else{
                    $orderArr = array();
                    $result = array();
                    $orderIds = $this->ordersyncstatusFactory->create()->getCollection()
                        ->addFieldToSelect('order_id')
                        ->addFieldToFilter('sync_status', 'no')
                        //->addFieldToFilter('payment_method', $paymentMethod)
                        //->addFieldToFilter('order_status', $orderStatus)
                        ->load()
                        ->getData();

                    //$logger->info('Orderss no: '.print_r($orderIds, true));

                    $arrUpdateMappingStatus = array();
                    foreach ($orderIds as $orderId) {
                        $order = $this->order->load($orderId['order_id']);
                        if ($order->getId()) {
                            $orderArr[] = $order;
                            $arrUpdateMappingStatus[] = $orderId['order_id'];
                        } else {
                            $status = $logModel::LOG_WARNING;
                            $logMsg[] = 'Order no longer exists.';
                        }
                    }
                    if (count($orderIds) > 0) {
                        $logMsg[] = 'Sync Started ';
                        $result = $helper->exportMultiOrders($orderArr, $logMsg);
                        $logMsg[] = 'Sync Executing ';
                    } else {
                        $logMsg[] = 'No orders to sync';
                    }
                    if ($result) {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                        $connection = $resource->getConnection();

                        $updateIds = implode(',', $arrUpdateMappingStatus);
                        $query_update = "UPDATE order_sync_status SET sync_status = 'yes', cc_cid = '', update_time = now() WHERE order_id in ($updateIds)";
                        $logMsg[] = "Mysql command: " . $query_update;
                        $connection->query($query_update);

                        $logMsg[] = 'Total of %d record(s) were successfully synced' . count($orderIds);
                        $logMsg[] = 'Status Updated ';
                    }
                }
            } catch (Exception $e) {
                $logMsg[] = $e->getMessage();
                $status = $logModel::LOG_FAIL;
            }
            $logMsg[] = 'Sync Finished ';

            if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
                $ipAddress = $_SERVER['REMOTE_ADDR'];
            } else {
                $ipAddress = '';
            }

            $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);
            
            $logger->info('logMsg::: ' . print_r($logMsg));
            $this->syncModel->setActivityType('order')
                ->setStartTime($start_time)
                ->setEndTime(date('Y-m-d H:i:s'))
                ->setStatus($status)
                ->setDescription(implode('<br />', $logMsg))
                ->setIpAddress($ipAddress)
                ->save();
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }
    }

    //check the status of order if it is completed or not
    private function checkOrderSyncingStatus($order, $mapPaymentOrderData)
    {
        if ((!$order->getStatus()) || ($order->getStatus() == '')) {
            return false;
        }
        $logFileName = "order_generation_" . date('Ymd') . '.log';
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        $orderStatus = $order->getStatus();
        $arrOrderStatus = array();
        // Mage::log('Order Id - '.$order->getId().'  Status - '.$order->getStatus(), null, $logFileName);
        foreach ($mapPaymentOrderData as $key => $mappingDetails) {
            if ($mappingDetails['payment_method'] == $paymentMethod) {
                $arrOrderStatus = explode(',', $mappingDetails['order_status']);
            }
        }

        if (!in_array($orderStatus, $arrOrderStatus)) {
            return false;
        }
        return true;
    }
}