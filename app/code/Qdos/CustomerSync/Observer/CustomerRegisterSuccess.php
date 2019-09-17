<?php
namespace Qdos\CustomerSync\Observer;
use Magento\Framework\Event\ObserverInterface;
 
class CustomerRegisterSuccess implements ObserverInterface
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
        /** when customer has registered then set the value as false so that customer_register_success event which gets called after this
         in qdosSync module will get executed **/
        $this->registry->unregister('customer_createpost');
        $this->registry->register('customer_createpost',false);
    }
}