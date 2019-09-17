<?php
namespace Neo\Mappaymentorder\Observer;

use Magento\Framework\Event\ObserverInterface;

class Mappaymentorder implements ObserverInterface
{
    protected $_messageManager;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configValue = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment_order_mapping/sync_order/enable');
        try{
        	$order_ids = $observer->getEvent()->getOrderIds()[0];
        	$_order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_ids);
            $payment = $_order->getPayment()->getData();

            $paymentMethod = $_order->getPayment()->getMethodInstance()->getCode();
            $orderStatus = $_order->getStatus();            
            // Mage::log('Saving Order in order_sync_status table with order #' . $_order->getIncrementId() . ' -> ' . $_order->getEntityId(), null, 'qdos-sync-order-' . date('Ymd') . '.log');
            $orderSyncStatusModel = $objectManager->get('Neo\Mappaymentorder\Model\Ordersyncstatus');
            $data['order_id'] = $_order->getEntityId();
            if ($configValue == 1) {
                $data['sync_status'] = 'no';
                if ($paymentMethod == 'ccsave') {
                    $data['cc_cid'] = json_encode($payment['cc_cid']);
                }
            }else{
                $data['sync_status'] = 'yes';
                $data['cc_cid'] = '';
            }
            $data['payment_method'] = $paymentMethod;
            $data['order_status'] = $orderStatus;
            $data['created_time'] = date("Y-m-d H:i:s");
            $orderSyncStatusModel->setData($data);
            $orderSyncStatusModel->save();
            $this->_messageManager->addSuccess(__('Added order status successfully.'));
           
        }catch(Exception $e){
            $this->_messageManager->addError(__('Error in Saving Order in order_sync_status table with order #' . $_order->getIncrementId() . ' -> ' . $_order->getEntityId() . ' with error '. $e->getMessage()));
            // Mage::log('Error in Saving Order in order_sync_status table with order #' . $_order->getIncrementId() . ' -> ' . $_order->getEntityId() . ' with error '. $e->getMessage(), null, 'qdos-sync-order-' . date('Ymd') . '.log');            
        }
    }

}