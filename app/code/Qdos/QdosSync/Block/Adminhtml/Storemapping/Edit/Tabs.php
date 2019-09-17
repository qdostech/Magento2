<?php
namespace Qdos\QdosSync\Block\Adminhtml\Storemapping\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('checkmodule_storemapping_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Storemapping Information'));
    }
}