<?php
/**
 *	@author Pradeep Sanku
 */
namespace Qdos\QdosSync\Cron;
use \Psr\Log\LoggerInterface;
use \Qdos\QdosSync\Helper\Data;

class SyncAttribute
{
	protected $_logger;
	protected $_dataHelper;
	public function __construct(LoggerInterface $logger,Data $dataHelper){
		$this->_logger = $logger;
		$this->_dataHelper = $dataHelper;
	}

	public function execute(){
        $this->_logger->info(__METHOD__);
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/testingCronSwapnil.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        //$logMsg = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $syncAttributeStatus = $this->_scopeConfig->getValue('qdosConfig/permissions/manual_sync_attribute',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($syncAttributeStatus) {
            $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            shell_exec('php bin/magento cache:clean');
            system('chmod -R 777 var/');

            if (strtolower($cronStatus) == 'running') {
                $logMsg = 'Another Sync already in progress. Please wait...';
                $this->_logger->info($logMsg);
            }else{
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);
$logger->info('in else swapnil : '.$cronStatus);
                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

                date_default_timezone_set('Asia/Kolkata');
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);
$logger->info('before syncStatus swapnil : ');
                $syncStatus = $this->_dataHelper->syncAttribute();

                if ($syncStatus == 'success') {
                    $logMsg = 'Qdos Attribute Sync Successful';
                    $this->_logger->info($logMsg);
                }else{
                    $logMsg = $syncStatus;
                    $this->_logger->info($logMsg);
                }
$logger->info('in syncStatus swapnil : '.$logMsg);
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