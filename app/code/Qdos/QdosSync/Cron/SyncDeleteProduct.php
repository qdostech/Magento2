<?php
/**
 *	@author Pradeep Sanku
 */
namespace Qdos\QdosSync\Cron;
use \Psr\Log\LoggerInterface;
use \Qdos\QdosSync\Helper\Product;

class SyncDeleteProduct
{
    protected $_logger;
    protected $_helperData;
    public function __construct(LoggerInterface $logger,
                                Product $productHelper){
        $this->_logger = $logger;
        $this->_helperProduct = $productHelper;
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
                $this->_logger->info($logMsg);
            } else {

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

                date_default_timezone_set('Asia/Kolkata');
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);

                $syncStatus = $this->_helperProduct->deleteProducts();

                if ($syncStatus == 'success') {
                    $logMsg = 'Qdos Delete Product Sync Successful';
                    $this->_logger->info($logMsg);
                } else {
                    $logMsg = $syncStatus;
                    $this->_logger->info($logMsg);
                }

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

            }
        } else {
            $logMsg = 'Manual Sync is Disabled.';
            $this->_logger->info($logMsg);
        }
        return true;
    }
}