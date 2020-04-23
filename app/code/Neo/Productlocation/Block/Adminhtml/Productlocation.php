<?php
namespace Neo\Productlocation\Block\Adminhtml;
class Productlocation extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    // protected function _construct()
    // {
		
    //     $this->_controller = 'adminhtml_productlocation';/*block grid.php directory*/
    //     $this->_blockGroup = 'Neo_Productlocation';
    //     $this->_headerText = __('Productlocation');
    //     $this->_addButtonLabel = __('Add Productlocation'); 
    //     parent::_construct();
		
    // }   

     /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
   /* protected function _prepareLayout()
    {

        
        $addButtonProps = [
            'id' => 'sync_productlocation',
            'label' => __('Sync Product Locations'),
            'onclick' => "setLocation('" . $this->getUrl('productlocation/productlocation/syncproductlocation') . "')"
        ];
        $this->buttonList->add('sync_productlocation', $addButtonProps);
        
        return parent::_prepareLayout();
    }
    */


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
            'label' => __('Sync Locations'),
            'onclick' => "setLocation('" . $this->_getSyncLocationUrl() . "')"
        ];
        $addButtonPropsNew = [
            'id' => 'sync_productlocationlogs',
            'class' => 'primary add',
            'label' => __('All Locations '),
            'onclick' => "setLocation('" . $this->_getSyncLocationLogsUrl() . "')"
        ];
        




        $this->buttonList->add('sync_productlocation', $addButtonProps);
        $this->buttonList->add('sync_productlocationlogs', $addButtonPropsNew);
        $this->buttonList->remove('add');
        return parent::_prepareLayout();
    }

    protected function _getSyncLocationUrl(){
        return $this->getUrl('productlocation/productlocation/syncproductlocation');
    }

    protected function _getSyncLocationLogsUrl(){
        return $this->getUrl('productlocation/alllocations/index');
    }

}
