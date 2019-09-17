<?php
namespace Qdos\CustomerSync\Observer;
use Magento\Framework\Event\ObserverInterface;
 
class CustomerSaveBefore implements ObserverInterface
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
        // if(Mage::getSingleton('customer/session')->isLoggedIn()){
        //     return;
        // }

        // $request = Mage::app()->getRequest();
        // $moduleName = $request->getModuleName();
        // $controllerName = $request->getControllerName();
        // $actionName = $request->getActionName();

        // $customer = $observer->getCustomer();
        // $path = $moduleName.$controllerName.$actionName;
         
        // if(($path == "onestepcheckoutindexindex" || $path == "customeraccountcreatepost")&& (!$customer->getIsAccount())){
        //     $customer->setIsAccount(0);
        //     $customer->setIsActive(1);
        // }
    }
}