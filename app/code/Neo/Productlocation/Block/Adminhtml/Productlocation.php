<?php
namespace Neo\Productlocation\Block\Adminhtml;
class Productlocation extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_productlocation';/*block grid.php directory*/
        $this->_blockGroup = 'Neo_Productlocation';
        $this->_headerText = __('Productlocation');
        $this->_addButtonLabel = __('Add Productlocation'); 
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
            'id' => 'sync_productlocation',
            'label' => __('Sync Product Locations'),
            'onclick' => "setLocation('" . $this->getUrl('productlocation/productlocation/syncproductlocation') . "')"
        ];
        $this->buttonList->add('sync_productlocation', $addButtonProps);
        
        return parent::_prepareLayout();
    }

}
