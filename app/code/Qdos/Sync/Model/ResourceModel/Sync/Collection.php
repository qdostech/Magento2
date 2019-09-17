<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Qdos\Sync\Model\ResourceModel\Sync;

/**
 * Syncs Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	 protected $_storeManager; 
	 protected $_resource;
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct(
    		//\Magento\Backend\Block\Template\Context $context,
    		//\Magento\Store\Model\StoreManagerInterface $storeManager,
    		//\Magento\Framework\App\ResourceConnection $resource
    	)
    {
    	//parent::__construct($context);
    	
        $this->_init('Qdos\Sync\Model\Sync', 'Qdos\Sync\Model\ResourceModel\Sync');
        /*$this->_storeManager = $storeManager;   
        $this->_resource = $resource;*/
    }

    /*public function addStoreFilter($store = null)
    {
        if ($store === null) {
            $store = $this->getStoreId();
        }

        //$store = Mage::app()->getStore($store);
        $store = $this->_storeManager->getStore($store);

        $this->addFieldToFilter('main_table.store_id',array('eq'=>$store->getId()));
        return $this;
    }

    public function addWebsiteIdToSelect(){
        //$resource = Mage::getSingleton('core/resource');
        $resource = $this->_resource->create();
        $this->getSelect()
            ->join(array('store' => $resource->getTableName('core/store')),
                'store.store_id = main_table.store_id',
                array('website_id'));
        return $this;
    }*/
}
