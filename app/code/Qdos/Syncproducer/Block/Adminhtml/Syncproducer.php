<?php
namespace Qdos\Syncproducer\Block\Adminhtml;
class Syncproducer extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_syncproducer';/*block grid.php directory*/
        $this->_blockGroup = 'Qdos_Syncproducer';
        $this->_headerText = __('Syncproducer');
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
            'id' => 'sync_producer',
            'label' => __('Sync Producer'),
            'onclick' => "setLocation('" . $this->_getSyncProducerUrl() . "')"
        ];
        $this->buttonList->add('sync_producer', $addButtonProps);
        
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getSyncProducerUrl()
    {
        return $this->getUrl(
            'syncproducer/syncproducer/newbutton'
        );
    }
    
}
