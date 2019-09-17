<?php
namespace Neo\Mappaymentorder\Controller\Adminhtml\Mappaymentorder;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
		
		 $ids = $this->getRequest()->getParam('mappaymentorder_id');
		if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addError(__('Please select mapping(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $row = $this->_objectManager->get('Neo\Mappaymentorder\Model\Mappaymentorder')->load($id);
					$row->delete();
				}
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
		 $this->_redirect('*/*/');
    }
}
