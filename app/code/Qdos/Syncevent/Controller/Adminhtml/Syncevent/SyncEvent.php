<?php
namespace Qdos\Syncevent\Controller\Adminhtml\Syncevent;
set_time_limit(0);
        ini_set('max_execution_time', 100000);
        ini_set('memory_limit', '2048M');
        ini_set('default_socket_timeout', 2000);
        
        ini_set('display_errors','On');
        if(!extension_loaded("soap"))
        {
          dl("php_soap.dll");
        } 


        ini_set("soap.wsdl_cache_enabled","0");


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


//use Psr\Log\LoggerInterface;



class SyncEvent extends \Magento\Backend\App\Action
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
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        PageFactory $resultPageFactory,
        \Qdos\QdosSync\Model\Log $log,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $time
         )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->directory_list = $directory_list;
        $this->_log = $log;
        $this->date = $date;
        $this->time = $time;
        //$this->logger = $logger;
    }

    public function execute()
    {
      $resultRedirect = $this->resultRedirectFactory->create();
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            shell_exec('php bin/magento cache:clean');
            system('chmod -R 777 var/');

            if (strtolower($cronStatus) == 'running') {
                $logMsg = 'Another Sync already in progress. Please wait...';
              //  $this->_log->info($logMsg);
                $this->messageManager->addError(__($logMsg));
            } else {

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

               // date_default_timezone_set('Asia/Kolkata');
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);

               $result = $objectManager->create('Qdos\Syncevent\Helper\Data')->importEvents();
                if ($result) {
                    $logMsg = 'Sync Event were synchronized success.';
                    //$this->_log->info($logMsg);
                    $this->messageManager->addSuccess(__($logMsg));
                } else {
                    $logMsg = 'Can not synchronize some events.';
                    //$this->_log->info($logMsg);
                    $this->messageManager->addError(__($logMsg));
                }

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

            }

      $resultRedirect->setPath('*/*/');     
      return $resultRedirect;
    }   


}
