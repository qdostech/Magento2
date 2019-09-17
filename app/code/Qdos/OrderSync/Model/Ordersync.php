<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */

namespace Qdos\OrderSync\Model;

use Magento\Framework\Exception\SynccategoriesException;

/**
 * Synccategoriestab synccategories model
 */
class Ordersync extends \Magento\Framework\Model\AbstractModel
{
/**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Qdos\OrderSync\Model\ResourceModel\Ordersync');
    }

    //sync all the orders which are pending
    public function syncOrders()
    {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order=$objectManager->create('Qdos\OrderSync\Helper\Order');
        $test = $order->syncOrders();
       // $syncStatus = $this->syncHelperData->syncOrders();

        echo "Model  called";die();

        // Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        // $userModel = Mage::getModel('admin/user');
        // $userModel->setUserId(0);
        // Mage::getSingleton('admin/session')->setUser($userModel);    

        // $syncOrderStatus = Mage::getStoreConfig('schedule_order_cron/sync_order/enable');

        // if ($syncOrderStatus) {
        //     $cronStatus = Mage::getStoreConfig('qdos_sync_config/current_cron_status/cron_status');

        //     if (strtolower($cronStatus) == 'running') {
        //         return false;
        //     }else{    
        //         $systemConfig = new Mage_Core_Model_Config();
        //         $systemConfig->saveConfig('qdos_sync_config/current_cron_status/cron_status', "Running", 'default');   
        //         $systemConfig = new Mage_Core_Model_Config();
        //         $currentDateTime = date('Y-m-d H:i:s', Mage::app()->getLocale()->storeTimeStamp());
        //         $systemConfig->saveConfig('qdos_sync_config/current_cron_status/cron_status_update_time', $currentDateTime, 'default');  
        //         $syncStatus = Mage::helper('ordersync/order')->syncOrders();

        //         $systemConfig = new Mage_Core_Model_Config();
        //         $systemConfig->saveConfig('qdos_sync_config/current_cron_status/cron_status', "Not Running", 'default');           
        //     }
        // }          
    } 

   
}