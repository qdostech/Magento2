<?php
namespace Neo\Productlocationqty\Block\Adminhtml;
class Productlocationqty extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_productlocationqty';
        $this->_blockGroup = 'Neo_Productlocationqty';
        $this->_headerText = __('Productlocationqty');
        $this->_addButtonLabel = __('Add Productlocationqty'); 
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
            'id' => 'sync_productlocationqty',
            'label' => __('Sync Product Locations Quantity'),
            'onclick' => "setLocation('" . $this->getUrl('productlocationqty/productlocationqty/syncproductlocationqty') . "')"
        ];
        $this->buttonList->add('sync_productlocationqty', $addButtonProps);
        
        return parent::_prepareLayout();
    }

}
