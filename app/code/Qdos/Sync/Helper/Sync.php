<?php
/**
* Qdos Sync
*
* @package       Qdos
* @subpackage   Sync
* @author        Shailendra Gupta
* @copyright    Copyright (c) 2013 - 2014 
* @since        Version 1.0
* @purpose      Sync related functionality 
**/
namespace Qdos\Sync\Helper;
//use Magento\Framework\App\Action\Context;

set_time_limit(0);
ignore_user_abort(true);
ini_set('max_execution_time', 30000);
ini_set('memory_limit', '2048M');
ini_set('default_socket_timeout', 4000);

class Sync extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_logger;
    public function __construct(
           \Magento\Framework\App\Helper\Context $context,
           \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
       parent::__construct($context);
        //$this->_logger = $logger; //$context->getLogger();  
        $this->_logger = $context->getLogger();
        $this->_storeManager = $storeManager;     
    }


    public function sendMailForSyncFailed($syncType)
    {
        // Transactional Email Template's ID
        //$templateId = Mage::getStoreConfig('qdos_sync_config/current_cron_status/sync_fail_template');
        $senderName = "Customer Support";
        $senderEmail = "support@qdos.com.au";
        $sender = array('name' => $senderName,
                    'email' => $senderEmail);
        // Set recepient information
        $recepientName = 'Support';
        //$recepientEmail = Mage::getStoreConfig('qdos_sync_config/current_cron_status/sync_fail_email_to');
        // Get Store ID    
        $storeId = $this->_storeManager->getStore()->getStoreId();
        // Set variables that can be used in email template
        $vars = array('hours' => $cronStatusCheckInterval, 'synctype' => $syncType);                 
        /*$translate  = Mage::getSingleton('core/translate');     
        // Send Transactional Email
        Mage::getModel('core/email_template')
            ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
                 
        $translate->setTranslateInline(true); */
    }
}