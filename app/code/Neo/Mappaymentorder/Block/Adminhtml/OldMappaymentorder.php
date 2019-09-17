<?php
namespace Neo\Mappaymentorder\Block\Adminhtml;
class Mappaymentorder extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_mappaymentorder';/*block grid.php directory*/
        $this->_blockGroup = 'Neo_Mappaymentorder';
        $this->_headerText = __('Mapping Payment Order Status');
        $this->_addButtonLabel = __('Add Mapping'); 
        parent::_construct();
		
    }   
}
