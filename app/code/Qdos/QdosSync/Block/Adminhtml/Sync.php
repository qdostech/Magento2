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

        $storeId = (int)$this->getRequest()->getParam('store', 0);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $syncPermissions = array();
        $arrSyncPerm = $objectManager->create('\Qdos\QdosSync\Model\Storemapping')
                       ->getCollection()
                       ->addFieldToSelect('sync_type')
                       ->addFieldToFilter('store_id', $storeId)
                       ->load()
                       ->getData();
                       
        if (!empty($arrSyncPerm[0]['sync_type'])){
            $syncPermissions = explode(',', $arrSyncPerm[0]['sync_type']);
        }

        $addButtonProps1 = [
            'id' => 'sync_price',
            'label' => __('Sync Price'),
            'onclick' => "setLocation('" . $this->_getSyncPriceUrl($storeId) . "')"
        ];

        $addButtonProps2 = [
            'id' => 'sync_stock',
            'label' => __('Sync Stock'),
            'onclick' => "setLocation('" . $this->_getSyncStockUrl($storeId) . "')"
        ];

        $addButtonProps3 = [
            'id' => 'sync_delete_product',
            'label' => __('Delete Products'),
            'onclick' => "setLocation('" . $this->_getDeleteSyncProductsUrl($storeId) . "')"
        ];

        $addButtonProps4 = [
            'id' => 'sync_products',
            'class' => 'primary add',
            'label' => __('Sync Products (Generate CSV)'),
            'onclick' => "setLocation('" . $this->_getSyncProductsUrl($storeId) . "')"
        ];
        
        if (in_array('Price', $syncPermissions)) {
            $this->buttonList->add('sync_price', $addButtonProps1);
        }
        if (in_array('Stock', $syncPermissions)) {
            $this->buttonList->add('sync_stock', $addButtonProps2);
        }
        if (in_array('Delete Product', $syncPermissions)) {
            $this->buttonList->add('sync_delete_product', $addButtonProps3);
        }
        if (in_array('Product', $syncPermissions)) {
            $this->buttonList->add('sync_products', $addButtonProps4);
        }
        $this->buttonList->remove('add');
        
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getSyncProductsUrl($storeId = 0)
    {
        return $this->getUrl(
            'qdossync/syncgrid/newbutton/store/'.$storeId
        );
    }

    protected function _getSyncPriceUrl($storeId = 0)
    {
        return $this->getUrl(
            'qdossync/syncgrid/updatePrice/store/'.$storeId
        );
    }

    protected function _getDeleteSyncProductsUrl($storeId = 0)
    {
        return $this->getUrl(
            'qdossync/syncgrid/syncDeleteProduct/store/'.$storeId
        );
    }

    protected function _getSyncStockUrl($storeId = 0)
    {
        return $this->getUrl(
            'qdossync/syncgrid/updateStock/store/'.$storeId
        );
    }
}
