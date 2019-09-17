<?php
namespace Qdos\CustomerSync\Controller\Adminhtml\Synccustomer;
use Magento\Backend\App\Action;
class NewAction extends \Magento\Backend\App\Action
{
     public function execute()
    {
		$this->_forward('edit');
    }
}