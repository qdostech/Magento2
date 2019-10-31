<?php
/**
 * @author Pradeep Sanku
 */

namespace Qdos\QdosSync\Cron;

use \Psr\Log\LoggerInterface;

class DeleteLogs
{
    protected $_logger;

    public function __construct(LoggerInterface $logger, \Qdos\QdosSync\Helper\Logs $logHelper)
    {
        $this->_logger = $logger;
        $this->logHelper = $logHelper;
    }

    /**
     * deleteLogs
     *
     * @author Ravi Mule
     * @access public
     * @params null
     * @return void
     **/
    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/deletelogcron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("delete log cron executed");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $deleteLogStatus = $this->_scopeConfig->getValue('qdosSync/autoDeleteLogs/auto_delete_logs', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($deleteLogStatus) {
            $logDays = $this->_scopeConfig->getValue('qdosSync/autoDeleteLogs/auto_delete_logs', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($logDays != '') {
                $deleteStatus = $this->logHelper->deleteLogs($logDays);
            } else {
                return false;
            }
        }
    }
}