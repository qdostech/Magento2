<?php
namespace Qdos\OrderSync\Controller\Adminhtml\Ordersync;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;


class SyncOrderStatus extends Action
{

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Qdos\OrderSync\Helper\Order\Status $ostatus,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->ostatus = $ostatus;
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $syncOrderStatus = $this->_scopeConfig->getValue('payment_order_mapping/permissions/order_status',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
        if ($syncOrderStatus) {
            $cronStatus = $this->_scopeConfig->getValue('qdos_sync_config/current_cron_status/cron_status',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if (strtolower($cronStatus) == 'running') {
                $this->_redirect('*/*/');
            }else{

                $this->_resourceConfig->saveConfig('qdosConfig_cron_status/cron_status/current_cron_status', "Running", 'default', 0); 
                $syncStatus = $this->ostatus->syncOrderStatus();

                if ($syncStatus == 'success') {
                    //Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sync')->__('Order Status Sync Successful'));
                }else{
                    //Mage::getSingleton('adminhtml/session')->addError($syncStatus);
                }
                $this->_resourceConfig->saveConfig('qdos_sync_config/current_cron_status/cron_status', "Not Running", 'default', 0); 
            }           
        }else{
            // Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sync')->__('Manual Sync is Disabled.'));
        }       
      
        $this->_redirect('*/*/index'); 
    }
  
}