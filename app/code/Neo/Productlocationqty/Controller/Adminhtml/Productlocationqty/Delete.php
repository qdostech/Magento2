<?php
namespace Neo\Productlocationqty\Controller\Adminhtml\Productlocationqty;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
		$id = $this->getRequest()->getParam('id');
		try {
				$Productlocationqty = $this->_objectManager->get('Neo\Productlocationqty\Model\Productlocationqty')->load($id);
				$Productlocationqty->delete();
                $this->messageManager->addSuccess(
                    __('Delete successfully !')
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
	    $this->_redirect('*/*/');
    }
}
