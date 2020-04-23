<?php
namespace Neo\Productlocationqty\Controller\Adminhtml\Productlocationqty;
set_time_limit(0);
ini_set('max_execution_time', 30000);
ini_set('memory_limit', '2048M');
ini_set('default_socket_timeout', 2000);

ini_set('display_errors', 'On');
if (!extension_loaded("soap")) {
    dl("php_soap.dll");
}


ini_set("soap.wsdl_cache_enabled", "0");

class SyncProductLocationqty extends \Magento\Backend\App\Action
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
         $synclocationqtyStatus = $this->_scopeConfig->getValue('qdosConfig/permissions/product_location_qty_sync',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($synclocationqtyStatus) { //$syncOrderStatus
            $cronStatus = $this->_scopeConfig->getValue('qdos_sync_config/current_cron_status/cron_status',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if (strtolower($cronStatus) == 'running') {
                $logMsg[] = 'Another Sync already in progress. Please wait...';
            }else{
                $this->_resourceConfig->saveConfig('qdosConfig_cron_status/cron_status/current_cron_status', "Running", 'default', 0);
                $syncStatus = $objectManager->get('\Neo\Productlocationqty\Helper\Getlocationqty')->syncGetLocationQty();
            if ($syncStatus == 'success') {
                $logMsg[] = 'Qdos Location QTY Sync Successful';
                $this->messageManager->addSucess('Qdos Product Location Qty Sync Successful.');
            }else{
                $logMsg[] = $syncStatus;
            }
            $this->_resourceConfig->saveConfig('qdos_sync_config/current_cron_status/cron_status', "Not Running", 'default', 0);
            }
        }else{
            $logMsg[] = 'Manual Sync is Disabled.';
           $this->messageManager->addError('Manual Sync is Disabled.');
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('productlocationqty/productlocationqty/index');
        return $resultRedirect;
    }
}