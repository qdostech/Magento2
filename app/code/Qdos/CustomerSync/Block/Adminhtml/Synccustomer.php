<?php
namespace Qdos\CustomerSync\Block\Adminhtml;
class Synccustomer extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		$this->_controller = 'adminhtml_synccustomer';/*block grid.php directory*/
        $this->_blockGroup = 'Qdos_CustomerSync';
        $this->_headerText = __('Synccustomer');
        $this->_addButtonLabel = __('Add New Entry');
        parent::_construct();
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'sync_products',
            'label' => __('Sync Customers'),
            'onclick' => "setLocation('" . $this->_getSyncCustomerUrl() . "')"
        ];
        $addButtonPropsNew = [
            'id' => 'sync_customergroup',
            'class' => 'primary add',
            'label' => __('Sync Customers Group'),
            'onclick' => "setLocation('" . $this->_getSyncCustomerGroupUrl() . "')"
        ];
        $this->buttonList->add('sync_cutomer', $addButtonProps);
        $this->buttonList->add('sync_cutomergroup', $addButtonPropsNew);
        $this->buttonList->remove('add');
        return parent::_prepareLayout();
    }

    protected function _getSyncCustomerUrl(){
        return $this->getUrl('customersync/synccustomer/newbutton');
    }

    protected function _getSyncCustomerGroupUrl(){
        return $this->getUrl('customersync/synccustomer/newgroup');
    }
}