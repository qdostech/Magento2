<?php
namespace Qdos\OrderSync\Block\Adminhtml\Ordersync\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {		
        parent::_construct();
        $this->setId('checkmodule_ordersync_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Sync Order By Status Information'));
    }
}