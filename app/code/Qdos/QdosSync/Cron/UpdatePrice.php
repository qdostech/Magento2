<?php
/**
 * @author Pradeep Sanku
 */

namespace Qdos\QdosSync\Cron;

use \Psr\Log\LoggerInterface;

class UpdatePrice
{
    protected $_logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    public function execute()
    {
        //$logMsg = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $syncStockStatus = $this->_scopeConfig->getValue('qdosSync/autoUpdatePrice/auto_update_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($syncStockStatus) {
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

                $syncStatus = $objectManager->create('Qdos\QdosSync\Helper\Data')->syncPrice();

                if ($syncStatus == 'success') {
                    $logMsg = 'Qdos Price Sync Successful';
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
            $logMsg = 'Auto Sync is Disabled.';
            $this->_logger->info($logMsg);
        }
        return true;
    }
}