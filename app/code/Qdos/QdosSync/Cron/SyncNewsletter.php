<?php
/**
 *	@author Pradeep Sanku
 */
namespace Qdos\QdosSync\Cron;
use \Psr\Log\LoggerInterface;

class SyncNewsletter
{
    protected $_logger;
    public function __construct(LoggerInterface $logger){
        $this->_logger = $logger;
        //add dependency of dataHelper
    }

    public function execute(){
        $this->_logger->debug('Cron Works in Delete Logs');
        return $this;
    }

    // changes added by Ravi Mule
    /*public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/newsletter-sync.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("newsletter sync log cron started");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $newsletterStatus = $this->_scopeConfig->getValue('qdosSync/autoSyncNewsletter/auto_sync_newsletter', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($newsletterStatus) {
            $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if (strtolower($cronStatus) == 'running') {
                return false;
            } else {
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);
                date_default_timezone_set('Asia/Kolkata');
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);

                $storeId = 0;
                $result = $this->dataHelper->exportNewsletter($storeId);
                if ($result) {
                    $logger->info("newsletter cron executed");
                } else {
                    $logger->info("something went wrong");
                }
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);
            }
        }
    }*/
}