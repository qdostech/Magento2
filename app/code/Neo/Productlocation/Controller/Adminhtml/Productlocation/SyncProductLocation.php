<?php
namespace Neo\Productlocation\Controller\Adminhtml\Productlocation;

class SyncProductLocation extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $logMsg = array(); 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $syncOrderStatus = $this->_scopeConfig->getValue('payment_order_mapping/permissions/order_status',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (true) { //$syncOrderStatus
            $cronStatus = $this->_scopeConfig->getValue('qdos_sync_config/current_cron_status/cron_status',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if (strtolower($cronStatus) == 'running') {
                $logMsg[] = 'Another Sync already in progress. Please wait...';
            }else{
                $this->_resourceConfig->saveConfig('qdosConfig_cron_status/cron_status/current_cron_status', "Running", 'default', 0);
                $syncStatus = $objectManager->get('\Neo\Productlocation\Helper\Getlocation')->syncGetLocation();
            if ($syncStatus == 'success') {
                $logMsg[] = 'Qdos Location Sync Successful';
            }else{
                $logMsg[] = $syncStatus;
            }
            $this->_resourceConfig->saveConfig('qdos_sync_config/current_cron_status/cron_status', "Not Running", 'default', 0);
            }
        }else{
            $logMsg[] = 'Manual Sync is Disabled.';
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('productlocation/productlocation/index');
        return $resultRedirect;
    }
}