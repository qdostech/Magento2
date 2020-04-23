<?php
namespace Qdos\CustomerSync\Block\Adminhtml;
class Synccustomer extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		$this->_controller = 'adminhtml_synccustomer';/*block grid.php directory*/
        $this->_blockGroup = 'Qdos_CustomerSync';
        $this->_headerText = __('Synccustomer');
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

        if (in_array('customer', $syncPermissions)) {

        $addButtonProps = [
            'id' => 'sync_cutomer',
            'label' => __('Sync Customers'),
            'onclick' => "setLocation('" . $this->_getSyncCustomerUrl($storeId) . "')"
        ];
          $this->buttonList->add('sync_cutomer', $addButtonProps);

    }
     if (in_array('customer_group', $syncPermissions)) {
        $addButtonPropsNew = [
            'id' => 'sync_customergroup',
            'class' => 'primary add',
            'label' => __('Sync Customers Group'),
            'onclick' => "setLocation('" . $this->_getSyncCustomerGroupUrl($storeId) . "')"
        ];
      
        $this->buttonList->add('sync_cutomergroup', $addButtonPropsNew);
    }
        $this->buttonList->remove('add');
        return parent::_prepareLayout();
    }

    protected function _getSyncCustomerUrl($storeId = 0){
        return $this->getUrl('customersync/synccustomer/newbutton/store/'.$storeId);
    }

    protected function _getSyncCustomerGroupUrl($storeId = 0){
        return $this->getUrl('customersync/synccustomer/newgroup/store/'.$storeId);
    }
}