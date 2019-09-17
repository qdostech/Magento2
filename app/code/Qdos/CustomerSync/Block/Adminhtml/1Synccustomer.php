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
        $this->_headerText = __('CustomerSync');
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
            'id' => 'sync_customer',
            'label' => __('Sync Customer'),
            'onclick' => "setLocation('" . $this->_getSyncCustomerUrl() . "')"
        ];
        $this->buttonList->add('sync_customer', $addButtonProps);
        
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getSyncCustomerUrl()
    {
        return $this->getUrl(
            'syncproducer/syncproducer/newbutton'
        );
    }   
}