<?php

namespace Qdos\QdosSync\Controller\Adminhtml\Syncproductmanual;
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

class Sync extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    protected $_scopeConfig;

    protected $_qdosHelper;

    protected $_writerInterface;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Qdos\QdosSync\Helper\Data $qdosHelper,
        \Magento\Framework\App\Config\Storage\WriterInterface $writerInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_qdosHelper = $qdosHelper;
        $this->_writerInterface = $writerInterface;
        $this->_datetime = $datetime;
    }

    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $resultRedirect = $this->resultRedirectFactory->create();
        $manualSyncProduct = $this->_scopeConfig->getValue('qdosConfig/permissions/manual_sync_product');
        if ($manualSyncProduct) {
            //$productIds = $this->getRequest()->getParam('sync');
             $productIds = !empty($this->getRequest()->getParam('sync'))?$this->getRequest()->getParam('sync'):$this->getRequest()->getParam('id');

            if (is_array($productIds)) {
                $productIds = implode('|', $productIds);
            }
            $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status');

            if (strtolower($cronStatus) == 'running') {
                $this->messageManager->addError(__('Another Sync already in progress. Please wait...'));
            } else {
                $systemConfig = $this->_writerInterface;
                $systemConfig->save('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);

                $syncStatus = $this->_qdosHelper->getProductExport($productIds, $storeId);

                if ($syncStatus == 'success') {
                    $this->messageManager->addSuccess(__('Product(s) Sync Successful'));
                } else {
                    $this->messageManager->addError($syncStatus);
                }

                $systemConfig->save('qdosConfig/cron_status/current_cron_status', "Not Running", 'default', 0);
                $currentDateTime = $this->_datetime->gmtDate();
                $systemConfig->save('qdosConfig/cron_status/current_cron_updated_time', $currentDateTime, 'default', 0);
            }
        } else {
            $this->messageManager->addError(__('Manual Sync is Disabled.'));
        }
        $resultRedirect->setPath('qdossync/syncproductmanual/index/');
        return $resultRedirect;
    }
}