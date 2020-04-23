<?php
namespace Neo\Productlocation\Controller\Adminhtml\Productlocation;

set_time_limit(0);
ini_set('max_execution_time', 30000);
ini_set('memory_limit', '2048M');
ini_set('default_socket_timeout', 2000);
        
ini_set('display_errors','On');
if(!extension_loaded("soap")){
    dl("php_soap.dll");
}

ini_set("soap.wsdl_cache_enabled","0");
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

class SyncProductLocation extends \Magento\Backend\App\Action
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


    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
      $logMsg = array(); 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $syncProductlocationStatus = $this->_scopeConfig->getValue('qdosConfig/permissions/product_location_sync',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($syncProductlocationStatus) { //$syncOrderStatus
            $cronStatus = $this->_scopeConfig->getValue('qdos_sync_config/current_cron_status/cron_status',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if (strtolower($cronStatus) == 'running') {
                $logMsg[] = 'Another Sync already in progress. Please wait...';
            }else{
                $this->_resourceConfig->saveConfig('qdosConfig_cron_status/cron_status/current_cron_status', "Running", 'default', 0);
                $syncStatus = $objectManager->get('\Neo\Productlocation\Helper\Getlocation')->syncGetLocation();


                
            if ($syncStatus == 'success') {
                $logMsg[] = 'Qdos Location Sync Successful';
            }else{
                $logMsg[] = $syncStatus;
            }
            $this->_resourceConfig->saveConfig('qdos_sync_config/current_cron_status/cron_status', "Not Running", 'default', 0);
            }
        }else{
                $this->messageManager->addError('Manual Sync is Disabled.');
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('productlocation/productlocation/index');
        return $resultRedirect;
        


    //      $resultRedirect = $this->resultRedirectFactory->create();
    // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //   /*Log code*/
    //   $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/productlocation.log');
    //   $logger = new \Zend\Log\Logger();
    //   $logger->addWriter($writer);

    //   $logger->info('In execute of SyncGetlocation controller');
    // $result = $objectManager->get('\Neo\Productlocation\Helper\Getlocation')->syncGetLocation(); //$objectManager->get('\Neo\Productlocation\Helper\Getlocation')
    
    // $resultRedirect->setPath('productlocation/productlocation/index');
    // return $resultRedirect;
    }
}