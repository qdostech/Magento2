<?php
/**
 * @author Pradeep Sanku
 */

namespace Qdos\QdosSync\Cron;

use \Psr\Log\LoggerInterface;

class Reindexing
{
    protected $_logger;

    public function __construct(LoggerInterface $logger, \Qdos\QdosSync\Model\Log $log)
    {
        $this->_logger = $logger;
        $this->_log = $log;
    }

    public function execute()
    {
        $logFilename = 'indexer-log' . date('Ymd') . '.log';
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/' . $logFilename);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("reindex log cron executed");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $autoReindex = $this->_scopeConfig->getValue('qdosSync/autoReindexing/auto_reindexing', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($autoReindex) {
            $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            shell_exec('php bin/magento cache:clean');
            system('chmod -R 777 var/');

            if (strtolower($cronStatus) == 'running') {
                return false;
            } else {

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);
                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

                //date_default_timezone_set('Asia/Kolkata');
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);

                $logMsgs = array();
                $start_time = date('Y-m-d H:i:s');
                $logModel = $objectManager->get('\Qdos\QdosSync\Model\Log');

                $logModel->setActivityType('auto_reindex')
                    ->setStartTime($start_time)
                    ->setStatus(\Neo\Winery\Model\Activity::LOG_PENDING)
                    //->setIpAddress($ipAddress)
                    ->save();
                $fail = 0;
                $success = 0;
                $indexer = $objectManager->create('\Magento\Indexer\Model\IndexerFactory')->create();
                $indexerCollection = $objectManager->create("\Magento\Indexer\Model\Indexer\CollectionFactory")->create();

                $ids = $indexerCollection->getAllIds();
                foreach ($ids as $id){
                    $idx = $indexer->load($id);
                    if ($idx->getStatus() != 'valid'){
                        try{
                            $idx->reindexRow($id);
                            //$idx->reindexAll();
                            $logMsgs[] = $process->getIndexerCode() . ' - Reinexed Successfully';
                            $success = 1;
                            $logger->info($process->getIndexerCode() . ' - Reinexed Successfully', null, $logFilename, true);
                        }
                        catch (Exception $e) {
                            $fail = 1;
                            $logMsgs[] = "<span style='color:red;'>" . $e->getMessage() . "</span>";
                            $logger->info($e->getMessage(), null, $logFilename, true);
                        }
                    }
                }
                
                if ($fail == '1' && $success == '1') {
                    $result = \Neo\Winery\Model\Activity::LOG_PARTIAL;
                } elseif ($fail == '1') {
                    $result = \Neo\Winery\Model\Activity::LOG_FAIL;
                } elseif ($success == '1') {
                    $result = \Neo\Winery\Model\Activity::LOG_SUCCESS;
                } else {
                    $logMsgs[] = 'Nothing to reindex';
                    $result = \Neo\Winery\Model\Activity::LOG_SUCCESS;
                }
                $logModel->setDescription(implode('<br />', $logMsgs))
                    ->setEndTime(date('Y-m-d H:i:s'))
                    ->setStatus($result)
                    ->save();

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);

                $currentDateTime = date('Y-m-d H:i:s');
                $this->_resourceConfig->saveConfig('qdos_sync_config/current_cron_status/cron_status_update_time', $currentDateTime, 'default', 0);
                //$cache_response_msg = Mage::helper('sync')->CacheRefresh();
            }
        } else {
            $logger->info("Auto Reindex not enabled", null, 'autoreindex.log', true);
        }
    }
}