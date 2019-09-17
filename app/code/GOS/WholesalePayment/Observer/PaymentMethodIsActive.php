<?php
namespace GOS\WholesalePayment\Observer;

use Magento\Framework\Event\ObserverInterface;
use GOS\WholesalePayment\Model\Payment;

class PaymentMethodIsActive implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
 
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        if ($customerSession->isLoggedIn()) {
            $customer = $customerSession->getCustomer();
            $event = $observer->getEvent();
            $method = $event->getMethodInstance();
            $result = $event->getResult();
            $customerDetails = $customer->getData();
            if (!isset($customerDetails['is_account'])) {
                if ($event->getQuote() && $method->getCode() == 'wholesale') {
                    $result->setData('is_available', false);
                }
            }
        }
    }
}