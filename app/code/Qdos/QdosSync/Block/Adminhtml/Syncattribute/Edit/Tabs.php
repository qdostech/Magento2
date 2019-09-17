<?php
namespace Qdos\QdosSync\Block\Adminhtml\Syncattribute\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('checkmodule_syncattribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Syncattribute Information'));
    }
}