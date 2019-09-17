<?php
namespace Qdos\CustomerSync\Observer;
use Magento\Framework\Event\ObserverInterface;
 
class SalesQuoteSaveBefore implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
      * @var \Magento\Framework\Registry
      */
    protected $_registry;

    protected $_logger;

    protected $_customerSession;
 
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager,
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
        $this->_logger = $logger;
        $this->_customerSession = $customerSession;
    }
 
    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        $customerSession = $this->_customerSession;
        if($customerSession->isLoggedIn()){
            $customerData = $customerSession->getCustomer()->getIsAccount();
            if($customerSession->getCustomer()->getIsAccount()){
                $quote->setIsAccount($customerSession->getCustomer()->getIsAccount());
            }

            $method = $quote->getPayment()->getMethod();
            if ($method){
                $quote->setPayByAccount($method);
            }

            /**
             * added this to fix the Guest template issue, which was encountered when the checkout person had becoming blank
             */
            if(!$quote->getCheckoutPerson() && $quote->getCustomerFirstname()){
                $quote->setCheckoutPerson($quote->getCustomerFirstname());
            }
        }
    }
}