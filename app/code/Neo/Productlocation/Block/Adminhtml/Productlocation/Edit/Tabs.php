<?php
namespace Neo\Productlocation\Block\Adminhtml\Productlocation\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
	protected function _construct()
	{
		parent::_construct();
		$this->setId('checkmodule_sync_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(__('Sync Information'));
	}
}