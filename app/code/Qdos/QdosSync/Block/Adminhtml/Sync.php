<?php
namespace Qdos\QdosSync\Block\Adminhtml;
class Sync extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_sync';/*block grid.php directory*/
        $this->_blockGroup = 'Qdos_QdosSync';
        $this->_headerText = __('Sync');
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
        $addButtonProps1 = [
            'id' => 'sync_price',
            'label' => __('Sync Price'),
            'onclick' => "setLocation('" . $this->_getSyncPriceUrl() . "')"
        ];

        $addButtonProps2 = [
            'id' => 'sync_stock',
            'label' => __('Sync Stock'),
            'onclick' => "setLocation('" . $this->_getSyncStockUrl() . "')"
        ];

        $addButtonProps3 = [
            'id' => 'sync_delete_product',
            'label' => __('Delete Products'),
            'onclick' => "setLocation('" . $this->_getDeleteSyncProductsUrl() . "')"
        ];

        $addButtonProps4 = [
            'id' => 'sync_products',
            'class' => 'primary add',
            'label' => __('Sync Products (Generate CSV)'),
            'onclick' => "setLocation('" . $this->_getSyncProductsUrl() . "')"
        ];
        
        $this->buttonList->add('sync_price', $addButtonProps1);
        $this->buttonList->add('sync_stock', $addButtonProps2);
        $this->buttonList->add('sync_delete_product', $addButtonProps3);
        $this->buttonList->add('sync_products', $addButtonProps4);
        $this->buttonList->remove('add');
        
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

    protected function _getSyncPriceUrl()
    {
        return $this->getUrl(
            'qdossync/syncgrid/updatePrice'
        );
    }

    protected function _getDeleteSyncProductsUrl()
    {
        return $this->getUrl(
            'qdossync/syncgrid/syncDeleteProduct'
        );
    }

    protected function _getSyncStockUrl()
    {
        return $this->getUrl(
            'qdossync/syncgrid/updateStock'
        );
    }
}
