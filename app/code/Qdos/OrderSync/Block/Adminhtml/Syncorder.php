<?php
namespace Qdos\OrderSync\Block\Adminhtml;

class Syncorder extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_syncorder';/*block grid.php directory*/
        $this->_blockGroup = 'Qdos_OrderSync';
        $this->_headerText = __('Syncorder');
        $this->_ButtonLabel = __('Add New Entry'); 
        parent::_construct();
		
    }

    protected function _prepareLayout()
    {

        
        $addButtonProps = [
            'id' => 'import_order_status',
            'label' => __('Import Order Status'),
            'onclick' => "setLocation('" . $this->_getSyncOrderUrl() . "')"
        ];
        $this->buttonList->add('import_order_status', $addButtonProps);
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getSyncOrderUrl()
    {
        return $this->getUrl(
            'ordersync/syncorder/syncorderstatus'
        );
    }
}