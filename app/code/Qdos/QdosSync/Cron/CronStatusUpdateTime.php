<?php
/**
 * @author Rahul Chavan
 */

namespace Qdos\QdosSync\Cron;

use \Psr\Log\LoggerInterface;
use \Qdos\QdosSync\Helper\Logs;

class CronStatusUpdateTime
{
    protected $_logger;
    protected $_scopeConfig;


    public function __construct(LoggerInterface $logger, \Qdos\QdosSync\Model\Log $log,
        Logs $logHelper,\Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
    \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool)
    {
        $this->_logger = $logger;
        $this->_log = $log;
        $this->_logHelper = $logHelper;
         $this->_cacheTypeList = $cacheTypeList;
    $this->_cacheFrontendPool = $cacheFrontendPool;    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
         $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
         if (strtolower($cronStatus) == 'running') 
         {
            $cronStatusUpdateTime = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_updated_time', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $cronStatusCheckInterval = $this->_scopeConfig->getValue('qdosConfig/cron_status/check_intervel_in_hours', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);        
            $cronStatusResetInterval =  $this->_scopeConfig->getValue('qdosConfig/cron_status/reset_cron_status_automatically', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);//Mage::getStoreConfig('qdos_sync_config/current_cron_status/reset_cron_status_automatically');
        //end
            $currentDateTime = date('Y-m-d H:i:s');
            $timeDiff = strtotime($currentDateTime) - strtotime($cronStatusUpdateTime);        

         if ($timeDiff > ($cronStatusResetInterval * 3600) && $cronStatusResetInterval != '') 
         {         

            $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);
            $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);
             // passthru("/bin/bash rename.sh");
                shell_exec('php bin/magento cache:clean');

                system('chmod -R 777 var/');   
        }

        $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (strtolower($cronStatus) == 'running') {

         // $this->_logHelper->sendMailForCronStatus();
        }

        //clean cache
        $types = array('config');//,'layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice');
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }



     }
    }

    
}