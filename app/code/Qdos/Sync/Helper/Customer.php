<?php
/**
 * Copyright © 2015 Qdos . All rights reserved.
 */
namespace Qdos\Sync\Helper;

// require_once 'Qdos/Sync/Helper/Data.php';

class Customer extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_limitPerSync = 100;
    protected $_defaultRecordPerSync = 10000;
    protected $_websites;
    protected $_attributes = array();
    protected $_fields;
    protected $_stores;
    protected $_customerGroups;
    protected $_systemFields = array();
    protected $_collection = array();
    protected $store_url;
    protected $_client;
    protected $_logMsg = array();
   
	public function __construct(
    \Magento\Framework\App\Helper\Context $context,
    \Magento\Framework\App\Filesystem\DirectoryList $directory_list
	) {
        $this->directory_list = $directory_list;
		parent::__construct($context);
        
	}

    public function getAllCustomerAttribute($customer = array()){
        $data = array();
        //$attributes = Mage::getModel('customer/entity_attribute_collection');
        // $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
        //         ->setEntityTypeFilter(Mage::getModel('eav/entity')->setType('customer')->getTypeId())
        //         ->addFilter("is_visible", 1);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $attributes = $objectManager->get('Magento\Customer\Model\Customer')->getAttributes();
        $isCustomer = !empty($customer) && $customer->getId();
        foreach ($attributes as $attribute) {
            if ($isCustomer){
                $data[$attribute->getAttributeCode()] = $customer->getData($attribute->getAttributeCode());
            }else{
                switch ($attribute->getBackendType()) {
                    case 'varchar':
                    case 'text':
                        $data[$attribute->getAttributeCode()] = '';
                        break;
                    case 'int':
                        $data[$attribute->getgAttributeCode()] = 0;
                        break;
                    case 'datetime':
                        $data[$attribute->getAttributeCode()] = time();
                        break;
                }
            }
        }
        $data = array_change_key_case($data, CASE_UPPER);
        return $data;
    }

   
    
}