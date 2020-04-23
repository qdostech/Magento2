<?php
namespace Neo\Productlocationqty\Controller\Adminhtml\Productlocationqty;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
		
		 $ids = $this->getRequest()->getParam('id');
		if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addError(__('Please select record(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $row = $this->_objectManager->get('Neo\Productlocationqty\Model\Productlocationqty')->load($id);
					$row->delete();
				}
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
		 $this->_redirect('*/allproductlocationqty/index');
    }
}
