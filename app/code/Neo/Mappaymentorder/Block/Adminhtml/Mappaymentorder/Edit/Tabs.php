<?php
namespace Neo\Mappaymentorder\Block\Adminhtml\Mappaymentorder\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('checkmodule_mappaymentorder_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Mappaymentorder Information'));
    }
}