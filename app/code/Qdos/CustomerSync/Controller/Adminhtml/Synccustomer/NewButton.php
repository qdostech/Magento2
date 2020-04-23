<?php

namespace Qdos\CustomerSync\Controller\Adminhtml\Synccustomer;
set_time_limit(0);
ini_set('max_execution_time', 30000);
ini_set('memory_limit', '2048M');
ini_set('default_socket_timeout', 2000);

ini_set('display_errors', 'On');
if (!extension_loaded("soap")) {
    dl("php_soap.dll");
}


ini_set("soap.wsdl_cache_enabled", "0");

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

//use Psr\Log\LoggerInterface;

class NewButton extends Action
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
        
        $this->resultPageFactory = $resultPageFactory;
        $this->_logger = $logger;
        parent::__construct($context);
    }


    ##Rahul Chavan

      public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $resultRedirect = $this->resultRedirectFactory->create();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $syncCustomerStatus = $this->_scopeConfig->getValue('qdosConfig/permissions/customer_manual_sync', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($syncCustomerStatus) {
            $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            shell_exec('php bin/magento cache:clean');
            system('chmod -R 777 var/');

            if (strtolower($cronStatus) == 'running') {
                $logMsg = 'Another Sync already in progress. Please wait...';
                $this->_logger->info($logMsg);
                $this->messageManager->addError(__($logMsg));
            } else {

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

               // date_default_timezone_set('Asia/Kolkata');
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);

                $result = $objectManager->get('Qdos\CustomerSync\Helper\Data')->syncCustomers($storeId);

                if ($result) {
                    $logMsg = 'Customers in store were synchronized success.';
                    $this->_logger->info($logMsg);
                    $this->messageManager->addSuccess(__($logMsg));
                } else {
                    $logMsg = 'Can not synchronize some Customers in this store.';
                    $this->_logger->info($logMsg);
                    $this->messageManager->addError(__($logMsg));
                }

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

            }
        } else {
            $logMsg = 'Manual Sync is Disabled.';
            $this->_logger->info($logMsg);
            $this->messageManager->addError(__($logMsg));
        }
        return $resultRedirect->setPath('*/*/');
    }


##old method which not checking cron running que and store
	/*public function execute()
    {

     $storeId = (int)$this->getRequest()->getParam('store', 0); 
      $resultRedirect = $this->resultRedirectFactory->create();  
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cData = $objectManager->get('Qdos\CustomerSync\Helper\Data')->syncCustomers($storeId);
        return $resultRedirect->setPath('*/
      //*/');
    //}
}