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
      $result = $objectManager->create('Qdos\Syncevent\Helper\Data')->importEvents();
     /* $objectManager->create('Magento\Framework\App\Request\Http')->setHeader('Content-Type','text/xml; charset=UTF-8')
                      ->setHeader('Content-Length',strlen("test test"))
                      ->setBody('test test');*/

      //exit('demo');

      $resultRedirect->setPath('*/*/');     
      return $resultRedirect;
    }   


}
