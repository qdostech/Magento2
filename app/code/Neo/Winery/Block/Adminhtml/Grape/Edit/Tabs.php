<?php
namespace Neo\Winery\Block\Adminhtml\Grape\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('checkmodule_grape_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Grape Information'));
    }
}