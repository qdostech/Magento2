<?php
/**
*	@author Pradeep Sanku
*/
namespace Qdos\QdosSync\Cron;
use \Psr\Log\LoggerInterface;
use \Qdos\QdosSync\Helper\Data;

class SyncCategory
{
	protected $_logger;
	protected $_dataHelper;
	public function __construct(LoggerInterface $logger,Data $dataHelper){
		$this->_logger = $logger;
		$this->_dataHelper = $dataHelper;
	}

	public function execute(){
        $this->_logger->info(__METHOD__);

        //$logMsg = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $syncCategoryStatus = $this->_scopeConfig->getValue('qdosSync/autoSyncCategory/auto_sync_category',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($syncCategoryStatus) {
            $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            shell_exec('php bin/magento cache:clean');
            system('chmod -R 777 var/');

            if (strtolower($cronStatus) == 'running') {
                $logMsg = 'Another Sync already in progress. Please wait...';
                $this->_logger->info($logMsg);
            }else{
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

                date_default_timezone_set('Asia/Kolkata');
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);

                $syncPermissions = $this->_dataHelper->getSyncPermission(0);
                if (in_array('Category', $syncPermissions)) {
                  //Mage::log('category sync executing for store id 0: ', null, 'storesync.log');
                  $syncStatus = $this->_dataHelper->syncCategory();
                }
                $storeManager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
                $allStores = $storeManager->getStores(true, false);
                foreach ($allStores as $storeId => $val)
                {
                    $syncPermissions = $this->_dataHelper->getSyncPermission($storeId);
                    if (in_array('Category', $syncPermissions)) {
                      //Mage::log('category sync executing for store id '.$storeId, null, 'storesync.log');
                      $syncStatus = $this->_dataHelper->syncCategory('', $storeId);
                    }                  
                }

                if ($syncStatus == 'success') {
                    $logMsg = 'Qdos Category Sync Successful';
                    $this->_logger->info($logMsg);
                }else{
                    $logMsg = $syncStatus;
                    $this->_logger->info($logMsg);
                }

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);
                
                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');
            }
        }else{
            $logMsg = 'Manual Sync is Disabled.';
            $this->_logger->info($logMsg);
        }

        //return $this;
    }
}