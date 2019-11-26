<?php
/**
 *	@author Pradeep Sanku
 */
namespace Qdos\QdosSync\Cron;
use \Psr\Log\LoggerInterface;
use \Qdos\QdosSync\Helper\Product;
use \Qdos\QdosSync\Helper\Data;
use \Qdos\QdosSync\Helper\Logs;

class SyncDeleteProduct
{
    protected $_logger;
    protected $_helperData;
    public function __construct(LoggerInterface $logger,
                                Product $productHelper,
                                Data $dataHelper,
                            Logs $logHelper){
        $this->_logger = $logger;
        $this->_helperProduct = $productHelper;
        $this->_dataHelper = $dataHelper;
        $this->_logHelper = $logHelper;
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $syncDeleteStatus = $this->_scopeConfig->getValue('qdosSync/autoSyncDeleteProduct/auto_delete_product', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($syncDeleteStatus) {
            $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            shell_exec('php bin/magento cache:clean');
            system('chmod -R 777 var/');

            if (strtolower($cronStatus) == 'running') {
                $logMsg = 'Another Sync already in progress. Please wait...';
                //$this->_logHelper->sendProcessFailureMail('Delete Products');
                $this->_logger->info($logMsg);
                return false;
            } else {

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

                date_default_timezone_set('Asia/Kolkata');
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);

                
                $syncPermissions = $this->_dataHelper->getSyncPermission(0);
                if (in_array('Delete Product', $syncPermissions)) {
                  //Mage::log('category sync executing for store id 0: ', null, 'storesync.log');
                  $syncStatus = $this->_helperProduct->deleteProducts();
                }
                $storeManager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
                $allStores = $storeManager->getStores();
                if (count($allStores) > 1) {
                    foreach ($allStores as $storeId => $val)
                    {
                        $syncPermissions = $this->_dataHelper->getSyncPermission($storeId);
                        if (in_array('Delete Product', $syncPermissions)) {
                          //Mage::log('category sync executing for store id '.$storeId, null, 'storesync.log');
                          $syncStatus = $this->_helperProduct->deleteProducts($storeId);
                        }
                    }
                }
                if ($syncStatus == 'success') {
                    $logMsg = 'Qdos Delete Product Sync Successful';
                    $this->_logger->info($logMsg);
                } else {
                    $logMsg = $syncStatus;
                    $this->_logger->info($logMsg);
                    //$this->_logHelper->sendMailForSyncFailed('Delete Product');
                }

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

            }
        } else {
            $logMsg = 'Auto Sync is Disabled.';
            $this->_logger->info($logMsg);
        }
        return true;
    }
}