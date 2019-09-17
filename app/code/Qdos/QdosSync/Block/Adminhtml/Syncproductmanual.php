<?php
namespace Qdos\QdosSync\Block\Adminhtml;
class Syncproductmanual extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_syncproductmanual';/*block grid.php directory*/
        $this->_blockGroup = 'Qdos_QdosSync';
        $this->_headerText = __('Sync product manually');
        $this->_addButtonLabel = __('Add New Entry'); 
        parent::_construct();
		$this->removeButton('add');
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {    
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getSyncProductsUrl()
    {
        return $this->getUrl(
            'qdossync/syncgrid/newbutton'
        );
    }
}
