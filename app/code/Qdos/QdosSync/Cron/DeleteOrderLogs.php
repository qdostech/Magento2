<?php
/**
 * @author Ravi Mule
 */

namespace Qdos\QdosSync\Cron;

use \Psr\Log\LoggerInterface;

class DeleteOrderLogs
{
    protected $_logger;

    public function __construct(LoggerInterface $logger, \Qdos\QdosSync\Helper\Logs $logHelper)
    {
        $this->_logger = $logger;
        $this->logHelper = $logHelper;
    }

    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/deleteordercron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("delete order log cron executed");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $deleteOrderLogStatus = $this->_scopeConfig->getValue('qdosSync/autoDeleteOrderLogs/auto_delete_order_logs', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($deleteOrderLogStatus) {
            $logDays = $this->_scopeConfig->getValue('qdosSync/autoDeleteOrderLogs/days_to_delete_order_logs', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($logDays != '') {
                $deleteStatus = $this->logHelper->deleteOrderLogs($logDays);
            } else {
                return false;
            }
        }
    }
}