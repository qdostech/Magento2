<?php

namespace Qdos\QdosSync\Controller\Adminhtml\SyncGrid;
set_time_limit(0);
ini_set('max_execution_time', 3000000);
ini_set('memory_limit', '2048M');
ini_set('default_socket_timeout', 2000000);
ini_set('display_errors', 'On');
if (!extension_loaded("soap")) {
    dl("php_soap.dll");
}
ini_set("soap.wsdl_cache_enabled", "0");

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

class UpdateStock extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LoggerInterface $logger
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_logger = $logger;
    }

    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $resultRedirect = $this->resultRedirectFactory->create();
        //$logMsg = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $syncStockStatus = $this->_scopeConfig->getValue('qdosConfig/permissions/manual_sync_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($syncStockStatus) {
            $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            shell_exec('php bin/magento cache:clean');
            system('chmod -R 777 var/');

            if (strtolower($cronStatus) == 'running') {
                $logMsg = 'Another Sync already in progress. Please wait...';
                $this->_logger->info($logMsg);
                $this->messageManager->addError($logMsg);
            } else {

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

                date_default_timezone_set('Asia/Kolkata');
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);

                $syncStatus = $objectManager->create('Qdos\QdosSync\Helper\Data')->syncQty($storeId);

                if ($syncStatus == 'success') {
                    $logMsg = 'Qdos Stock Sync Successful';
                    $this->_logger->info($logMsg);
                    $this->messageManager->addSuccess($logMsg);
                } else {
                    $logMsg = $syncStatus;
                    $this->messageManager->addError($logMsg);
                    $this->_logger->info($logMsg);
                }

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

            }
        } else {
            $logMsg = 'Manual Sync is Disabled.';
            $this->_logger->info($logMsg);
            $this->messageManager->addError($logMsg);
        }
        return $resultRedirect->setPath('*/*/');
    }
}