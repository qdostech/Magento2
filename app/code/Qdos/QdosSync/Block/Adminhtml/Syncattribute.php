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
        
        if (in_array('Attribute', $syncPermissions)) {
            $addButtonProps = [
                'id' => 'sync_attributes',
                'class' => 'primary add',
                'label' => __('Sync Attributes (Generate CSV)'),
                'onclick' => "setLocation('" . $this->_getSyncAttributesUrl($storeId) . "')"
            ];
            $this->buttonList->add('sync_attributes', $addButtonProps);
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
    protected function _getSyncAttributesUrl($storeId = 0)
    {
        return $this->getUrl(
            'qdossync/syncattribute/syncattribute/store/'.$storeId
        );
    }
}
