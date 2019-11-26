<?php
/**
 * @author Ravi Mule
 */

namespace Qdos\QdosSync\Cron;

use \Psr\Log\LoggerInterface;
use \Qdos\QdosSync\Helper\Logs;

class UpdateStock
{
    protected $_logger;

    public function __construct(LoggerInterface $logger, Logs $logHelper)
    {
        $this->_logger = $logger;
        $this->_logHelper = $logHelper;
    }

    public function execute()
    {
        //$logMsg = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $syncStockStatus = $this->_scopeConfig->getValue('qdosSync/autoUpdateStock/auto_update_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($syncStockStatus) {
            $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            shell_exec('php bin/magento cache:clean');
            system('chmod -R 777 var/');

            if (strtolower($cronStatus) == 'running') {
                $logMsg = 'Another Sync already in progress. Please wait...';
                //$this->_logHelper->sendProcessFailureMail('Stock Sync');
                $this->_logger->info($logMsg);
                return false;
            } else {

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

                date_default_timezone_set('Asia/Kolkata');
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);

                $syncPermissions = $objectManager->create('Qdos\QdosSync\Helper\Data')->getSyncPermission(0);
                if (in_array('Stock', $syncPermissions)) {
                    //Mage::log('category sync executing for store id 0: ', null, 'storesync.log');
                    $syncStatus = $objectManager->create('Qdos\QdosSync\Helper\Data')->syncQty();
                }
                $storeManager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
                $allStores = $storeManager->getStores();
                if (count($allStores) > 1) {
                    foreach ($allStores as $storeId => $val)
                    {
                        $syncPermissions = $objectManager->create('Qdos\QdosSync\Helper\Data')->getSyncPermission($storeId);
                        if (in_array('Stock', $syncPermissions)) {
                            //Mage::log('category sync executing for store id '.$storeId, null, 'storesync.log');
                            $syncStatus = $objectManager->create('Qdos\QdosSync\Helper\Data')->syncQty($storeId);
                        }
                    }
                }
                if ($syncStatus == 'success') {
                    $logMsg = 'Qdos Stock Sync Successful';
                    $this->_logger->info($logMsg);
                } else {
                    $logMsg = $syncStatus;
                    $this->_logger->info($logMsg);
                    //$this->_logHelper->sendMailForSyncFailed('Stock');
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