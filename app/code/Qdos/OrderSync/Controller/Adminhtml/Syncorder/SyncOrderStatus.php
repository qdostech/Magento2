<?php
namespace Qdos\OrderSync\Controller\Adminhtml\Syncorder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class SyncOrderStatus extends Action
{

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Qdos\OrderSync\Helper\Order\Status $ostatus,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->ostatus = $ostatus;
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');

        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $syncOrderStatus = $this->_scopeConfig->getValue('payment_order_mapping/permissions/order_status',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($syncOrderStatus) {
            $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
            if (strtolower($cronStatus) == 'running') {
                $this->messageManager->addError(__('Another Sync already in progress. Please wait...'));
                $this->_redirect('*/*/');
            }else{
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);
                $syncStatus = $this->ostatus->syncOrderStatus();
                if ($syncStatus == 'success') {
                    $this->messageManager->addSuccess(__('Order Status Sync Successful'));
                }else{
                    $this->messageManager->addError($syncStatus);
                }
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);
            }
        }else{
            $this->session->addError(__('Manual Sync is Disabled.'));
        }
        $this->_redirect('*/*/index'); 
    }
}