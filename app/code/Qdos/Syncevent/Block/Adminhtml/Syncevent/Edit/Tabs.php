<?php
namespace Qdos\Syncevent\Block\Adminhtml\Syncevent\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('checkmodule_syncevent_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Syncevent Information'));
    }
}