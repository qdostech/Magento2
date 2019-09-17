<?php
namespace Qdos\CustomerSync\Observer;
use Magento\Framework\Event\ObserverInterface;
 
class CustomerCreatePost implements ObserverInterface
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
        /** if customer register is called than do not call the export Customer function */
        $customerCreatepostRegistry = $this->_registry->registry('customer_createpost');
        if(!($customerCreatepostRegistry)) {
            $this->_registry->register('customer_createpost',true);
        }
    }
}