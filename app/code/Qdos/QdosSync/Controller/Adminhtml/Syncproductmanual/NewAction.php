<?php
namespace Qdos\QdosSync\Controller\Adminhtml\Syncproductmanual;
use Magento\Backend\App\Action;
class NewAction extends \Magento\Backend\App\Action
{
     public function execute()
    {
		$this->_forward('edit');
    }
}
