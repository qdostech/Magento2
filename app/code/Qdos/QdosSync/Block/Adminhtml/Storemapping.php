<?php
namespace Qdos\QdosSync\Block\Adminhtml;
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
        $this->_blockGroup = 'Qdos_QdosSync';
        $this->_headerText = __('Storemapping');
        $this->_addButtonLabel = __('Add New Entry'); 
        parent::_construct();
		
    }
}
