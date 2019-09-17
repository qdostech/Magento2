<?php
namespace Neo\Mappaymentorder\Controller\Adminhtml\Mappaymentorder;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
		$id = $this->getRequest()->getParam('id');
		try {
				$mappaymentorder = $this->_objectManager->get('Neo\Mappaymentorder\Model\Mappaymentorder')->load($id);
				$mappaymentorder->delete();
                $this->messageManager->addSuccess(
                    __('Delete successfully !')
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
	    $this->_redirect('*/*/');
    }
}
