<?php

namespace Qdos\QdosSync\Observer;

use Magento\Framework\Event\ObserverInterface;

class NewsletterSubscribeAfter implements ObserverInterface
{
	protected $_customerSession;
	protected $_qdosHelper;
	
	public function __construct(
	    \Magento\Store\Model\StoreManagerInterface $storeManager, 
	    \Magento\Customer\Model\SessionFactory $customerSession,
	    \Magento\Framework\App\ResourceConnection $resource,
	    \Qdos\QdosSync\Helper\Data $qdosHelper
	) {
	    $this->_storeManager = $storeManager;
	    $this->_resource = $resource;
	    $this->_qdosHelper = $qdosHelper;
	     $this->_customerSession = $customerSession->create();
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

		$response="";
    	$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/newsletter-sync.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("newsletter_sync_start");

        $subscriber = $observer->getEvent()->getSubscriber();
 		$logger->info("Subscriber_Email--".$observer->getEvent()->getSubscriber()->getSubscriberEmail());             
        // $_order     = $observer->getOrder();
        // if(!$_order){
        //     return;
        // }
        //$logger->info($observer->getEvent()->getName());
        if ($this->_customerSession->isLoggedIn()) {
            $customer= $this->_customerSession->getCustomer();       
           $response= $this->_qdosHelper->exportCustomer($customer);            
        }else{

            if ($subscriber->getCustomerId() == 0){
            	
               $response= $this->_qdosHelper->exportCustomerNonRegisteredFromSubscriber($subscriber);

            }

        }
         $logger->info("newsletter_sync_end response msg--".$response);




    }

    
}