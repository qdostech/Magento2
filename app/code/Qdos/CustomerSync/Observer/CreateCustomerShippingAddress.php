<?php
namespace Qdos\CustomerSync\Observer;
use Magento\Framework\Event\ObserverInterface;
 
class CreateCustomerShippingAddress implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
      * @var \Magento\Framework\Registry
      */
    protected $_registry;
 
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager,
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry
    ) {
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
    }
 
    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // need to convert this below code to magento 2
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId(); 
        $customer   = Mage::getModel('customer/customer')->load($customerId);

        $billingAddressId  = (int) $customer->getDefaultBilling();
        $shippingAddressId = (int) $customer->getDefaultShipping();
        
        if ($billingAddressId == $shippingAddressId) {
            Mage::register('customer_createshipment',true);
            Mage::helper('sync')->createShippingAddress($customerId,$shippingAddressId);
        }
    }
}