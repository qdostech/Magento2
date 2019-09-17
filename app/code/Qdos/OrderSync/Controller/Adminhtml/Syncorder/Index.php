<?php
namespace Qdos\OrderSync\Controller\Adminhtml\Syncorder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


class Index extends Action
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
        \Qdos\OrderSync\Model\Ordersync $ordersync
    )
    {
        parent::__construct($context);
        $this->ordersync = $ordersync;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $this->resultPage = $this->resultPageFactory->create();  
        $this->resultPage->setActiveMenu('Qdos_Ordersync::syncorder');
        $this->resultPage ->getConfig()->getTitle()->set((__('Syncorder')));
        return $this->resultPage;
    }
  
}