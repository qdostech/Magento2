<?php
namespace Neo\Storemapping\Block\Adminhtml;
class Storemapping extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_storemapping';/*block grid.php directory*/
        $this->_blockGroup = 'Neo_Storemapping';
        $this->_headerText = __('Storemapping');
        $this->_addButtonLabel = __('Add Storemapping'); 
        parent::_construct();
		
    }   
}
