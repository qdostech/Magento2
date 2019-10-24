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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $store = $storeManager->getStore($storeId);
        //echo "<pre>";print_r($store->getData());exit;
        //$storeManager->setCurrentStore($store->getCode());

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

        if (in_array('Category', $syncPermissions)) {
            $addButtonProps = [
                'id' => 'sync_categories',
                'class' => 'primary add',
                'label' => __('Sync Categories (Generate CSV)'),
                'onclick' => "setLocation('" . $this->_getSyncCategoriesUrl($storeId) . "')"
            ];
            $this->buttonList->add('sync_categories', $addButtonProps);
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
    protected function _getSyncCategoriesUrl($storeId = 0)
    {
        return $this->getUrl(
            'qdossync/synccategories/synccategories/store/'.$storeId
        );
    }
}
