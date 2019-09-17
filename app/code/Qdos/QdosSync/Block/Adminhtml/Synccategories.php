<?php
namespace Qdos\QdosSync\Block\Adminhtml;
class Synccategories extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_synccategories';/*block grid.php directory*/
        $this->_blockGroup = 'Qdos_QdosSync';
        $this->_headerText = __('Import Categories Management');
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
            'id' => 'sync_categories',
            'label' => __('Sync Categories (Generate CSV)'),
            'onclick' => "setLocation('" . $this->_getSyncCategoriesUrl() . "')"
        ];
        $this->buttonList->add('sync_categories', $addButtonProps);
        
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getSyncCategoriesUrl()
    {
        return $this->getUrl(
            'qdossync/synccategories/synccategories'
        );
    }
}
