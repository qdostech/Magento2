<?php

namespace Neo\Mappaymentorder\Controller\Adminhtml\Mappaymentorder;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($data) {
            $model = $this->_objectManager->create('Neo\Mappaymentorder\Model\Mappaymentorder');
            $id = $this->getRequest()->getParam('mappaymentorder_id');
            if ($id) {
                $model->load($id);
            }
            if ($data['order_status']) {
                $data['order_status'] = implode(',', $data['order_status']);
            }

            if ($data['order_status_invoice']) {
                $data['order_status_invoice'] = implode(',', $data['order_status_invoice']);
            }
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccess(__('Mappaymentorder Has been Saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getMappaymentorderId(), '_current' => true));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving Mappayment order.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', array('mappaymentorder_id' => $this->getRequest()->getParam('mappaymentorder_id')));
            return;
        }
        $this->_redirect('*/*/');
    }
}
