<?php
namespace Qdos\QdosSync\Block\Adminhtml;
class Syncattribute extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_syncattribute';/*block grid.php directory*/
        $this->_blockGroup = 'Qdos_QdosSync';
        $this->_headerText = __('Syncattribute');
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
            'id' => 'sync_attributes',
            'label' => __('Sync Attributes (Generate CSV)'),
            'onclick' => "setLocation('" . $this->_getSyncAttributesUrl() . "')"
        ];
        $this->buttonList->add('sync_attributes', $addButtonProps);
        
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getSyncAttributesUrl()
    {
        return $this->getUrl(
            'qdossync/syncattribute/syncattribute'
        );
    }
}
