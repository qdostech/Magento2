<?php
namespace Qdos\OrderSync\Block\Adminhtml;

class Ordersync extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_ordersync';/*block grid.php directory*/
        $this->_blockGroup = 'Qdos_OrderSync';
        $this->_headerText = __('Ordersync');
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

        
        $syncorderstatus = [
            'id' => 'syncorderstatus',
            'label' => __('Import Order Status'),
            'onclick' => "setLocation('" . $this->getUrl('ordersync/ordersync/syncorderstatus') . "')"
        ];
        $this->buttonList->add('syncorderstatus', $syncorderstatus);

        $syncorders = [
            'id' => 'syncorders',
            'label' => __('Sync Orders'),
            'onclick'   => "var cheked_values = [];
                                var inputs = document.getElementsByClassName('massaction-checkbox');
                                    for (var i = 0; i < inputs.length; i += 1) {
                                        if(inputs[i].checked) {// if checked
                                            cheked_values.push(inputs[i].value);
                                        }
                                    }
                                if(cheked_values.length == 0){
                                    alert('Please select order status to sync.');
                                    return false;
                                }else{
                                    var serializedArr = JSON.stringify(cheked_values);
                                    setLocation('".$this->getUrl('*/*/syncorders')."data/'+serializedArr);
                                }
                                "
        ];
        $this->buttonList->add('syncorders', $syncorders);
        
        return parent::_prepareLayout();
    }
}