<?php

namespace Qdos\CustomerSync\Controller\Adminhtml\Synccustomer;
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

//use Psr\Log\LoggerInterface;

class NewButton extends \Magento\Backend\App\Action
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
        PageFactory $resultPageFactory )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        //$this->logger = $logger;
    }

	public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();  
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cData = $objectManager->get('Qdos\CustomerSync\Helper\Data')->syncCustomers();
        return $resultRedirect->setPath('*/*/');
    }
}