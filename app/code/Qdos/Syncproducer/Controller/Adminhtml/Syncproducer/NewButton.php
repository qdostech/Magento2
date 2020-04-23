<?php

namespace Qdos\Syncproducer\Controller\Adminhtml\Syncproducer;
set_time_limit(0);
ini_set('max_execution_time', 30000);
ini_set('memory_limit', '2048M');
ini_set('default_socket_timeout', 2000);

ini_set('display_errors', 'On');
if (!extension_loaded("soap")) {
    dl("php_soap.dll");
}


ini_set("soap.wsdl_cache_enabled", "0");


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


//use Psr\Log\LoggerInterface;


class NewButton extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        PageFactory $resultPageFactory,
        \Qdos\QdosSync\Model\Log $log
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->directory_list = $directory_list;
        $this->_log = $log;
        //$this->logger = $logger;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $cronStatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            shell_exec('php bin/magento cache:clean');
            system('chmod -R 777 var/');

            if (strtolower($cronStatus) == 'running') {
                $logMsg = 'Another Sync already in progress. Please wait...';
              //  $this->_log->info($logMsg);
                $this->messageManager->addError(__($logMsg));
            } else {

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

               // date_default_timezone_set('Asia/Kolkata');
                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_updated_time', date("Y-m-d H:i:s"), 'default', 0);  



                $base = $this->directory_list->getPath('lib_internal');
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $lib_file = $base . '/Test.php';
                require_once($lib_file);
                $client = Test();
                $resultClient = $client->connect();
                $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');
                try {

                    $result=$this->importProducer($store_id = 1, $resultClient, $store_url, $client);

                   // return $resultRedirect->setPath('*/*/');
                     } catch (Exception $e) {
                }
                 if ($result) {
                    $logMsg = 'Sync Producer were synchronized success.';
                    //$this->_log->info($logMsg);
                    $this->messageManager->addSuccess(__($logMsg));
                } else {
                    $logMsg = 'Can not synchronize some Producers.';
                    //$this->_log->info($logMsg);
                    $this->messageManager->addError(__($logMsg));
                }

                $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "not running", 'default', 0);

                shell_exec('php bin/magento cache:clean');
                system('chmod -R 777 var/');

            }

      $resultRedirect->setPath('*/*/');     
      return $resultRedirect;

    }

    public function importProducer($store_id = 1, $resultClient, $store_url, $clientLog)
    {



        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logModel = $objectManager->get('\Qdos\Sync\Model\Sync');
        $_result = $logModel::LOG_SUCCESS;
        $start_time = date('Y-m-d H:i:s');
        $logMsgs = $logMsg = $productLogIds = $hiddenProductArr = array();
        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipAddress = '';
        }
        $logModel->setActivityType('producer')
            ->setStartTime($start_time)
            ->setStatus($logModel::LOG_PENDING)
            ->setIpAddress($ipAddress)
            ->save();


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $client = $resultClient->GetProducer(array('STORE_URL' => $store_url));
        $logFileName = "syncProducer-" . date('Ymd') . ".log";

        $clientLog->setLog("Sync Producer Started. ", null, $logFileName);
        $logMsg[] = "Sync Producer Started. ";

        if ($client->outErrorMsg && strlen($client->outErrorMsg) > 0) {
            $error = true;
            $clientLog->setLog("Error while sync producer ", null, $logFileName);
            $logMsg[] = "Sync Producer Error. ";
        } else {
            $result = $client->GetProducerResult;
            if (is_object($result) && isset($result->Producer)) {
                $_result = $this->convertObjToArray($result->Producer);

                 $logMsg[]="Total Records :". count($_result) ."";
                $clientLog->setLog("--Count--" . count($_result), null, $logFileName);
                $success = 0;
                $fail = 0;$new=0;$update=0;
                try {
                    foreach ($_result as $producer) {
                        if (isset($producer['producer_id']) and is_numeric($producer['producer_id'])) {

                            $_producer = $objectManager->create('\Qdos\Syncproducer\Model\Syncproducer');
                            $_producer_load = $_producer->load($producer['producer_id']);

                            if (!$_producer_load->getId()) {
                                $qdosId = $producer['producer_id'];
                            }

                            try {
                                if ($_producer_load->getId() != $producer['producer_id']) {
                                    if (isset($producer['description'])) {
                                        $_producer->setDescription(trim($producer['description']));
                                    } else {
                                        $_producer->setDescription('');
                                    }
                                    if (isset($producer['producer_rich_name'])) {
                                        $_producer->setProducerRichName(trim($producer['producer_rich_name']));
                                    } else {
                                        $_producer->setProducerRichName('');
                                    }

                                    $_producer->setData($producer);
                                    $_producer->save();

                                    $newId = $_producer->getId();
                                    $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
                                    $connection = $this->_resources->getConnection();
                                    $Table = $this->_resources->getTableName('syncproducer_syncproducer');
                                    // Update query
                                    $write = "UPDATE `" . $Table . "` SET `id`= " . $producer['producer_id'] . " WHERE `id`= " . $newId;

                                    $connection->query($write);

                                    $clientLog->setLog($producer['producer_id'] . " -- saved successfully", null, $logFileName);
                                    $new++;

                                } else {
                                    if (isset($producer['description'])) {
                                        $_producer_load->setDescription(trim($producer['description']));
                                    } else {
                                        $_producer_load->setDescription('');
                                    }
                                    if (isset($producer['producer_rich_name'])) {
                                        $_producer_load->setProducerRichName(trim($producer['producer_rich_name']));
                                    } else {
                                        $_producer_load->setProducerRichName('');
                                    }

                                    $_producer_load->save();

                                    $clientLog->setLog($producer['producer_id'] . " -- Updated successfully", null, $logFileName);
                                    $update++;
                                }
                                $success = 1;
                              //  $logMsg[] = $producer['producer_id'] . " --  Sync Producer ";
                            } catch (Exception $e) {
                                $error = true;
                                $fail = 1;
                                $clientLog->setLog("-----in inner catch---", null, $logFileName);
                                $logMsg[] = 'Error in processing';
                            }

                        }
                    }
                } catch (Exception $e) {
                    $error = true;
                    $clientLog->setLog("-----in Outer catch---", null, $logFileName);
                }

                if ($fail == '1' && $success == '1') {
                    $result = \Qdos\QdosSync\Model\Activity::LOG_PARTIAL;
                } elseif ($fail == '1') {
                    $result = \Qdos\QdosSync\Model\Activity::LOG_FAIL;
                } elseif ($success == '1') {
                    $result = \Qdos\QdosSync\Model\Activity::LOG_SUCCESS;
                } else {
                    $result = \Qdos\QdosSync\Model\Activity::LOG_SUCCESS;
                }
               
                  $logMsg[] = "Total New Created : ".$new;
                    $logMsg[] = "Total Update : ".$update;

                $logModel->setEndTime(date('Y-m-d H:i:s'))
                    ->setStatus($result)
                    ->setDescription(implode('<br />', $logMsg))
                    ->save();

                    return $result;
            }
        }

    }

    public function convertObjToArray($object)
    {
        $new = array();
        if (is_object($object)) {
            $new[] = array_change_key_case((array)$object, CASE_LOWER);
        }
        if (is_array($object)) {
            foreach ($object as $obj) {
                if (is_object($obj)) {
                    $new[] = array_change_key_case((array)$obj, CASE_LOWER);
                }
            }
        }
        return $new;
    }


}
