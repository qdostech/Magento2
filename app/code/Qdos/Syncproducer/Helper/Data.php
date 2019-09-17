<?php
/**
 * Copyright © 2015 Qdos . All rights reserved.
 */

namespace Qdos\Syncproducer\Helper;
set_time_limit(0);
ini_set('max_execution_time', 100000);
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

// use \Psr\Log\LoggerInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    // protected $_logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    // public function __construct(\Magento\Framework\App\Helper\Context $context
    // ) {
    // 	parent::__construct($context);
    // }
    public function __construct(\Magento\Framework\App\Helper\Context $context,
                                \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
                                PageFactory $resultPageFactory,
                                \Qdos\QdosSync\Model\Log $log,
                                \Magento\Framework\Stdlib\DateTime\DateTime $date,
                                \Magento\Framework\Stdlib\DateTime\TimezoneInterface $time
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->directory_list = $directory_list;
        $this->_log = $log;
        $this->date = $date;
        $this->time = $time;
        // $this->_logger = $context->$logger;
    }

    public function importProducer()
    {
        // $this->_logger->info(__METHOD__);
        $store_id = 1;
        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base . '/Test.php';
        require_once($lib_file);
        $clientLog = Test();
        $resultClient = $clientLog->connect();
        $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');

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
                $clientLog->setLog("--Count--" . count($_result), null, $logFileName);
                $success = 0;
                $fail = 0;
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
                                }
                                $success = 1;
                                $logMsg[] = $producer['producer_id'] . " --  Sync Producer ";
                            } catch (Exception $e) {
                                $error = true;
                                $fail = 1;
                                $logMsg[] = 'Error in processing';
                            }
                        }
                    }
                    $this->reindexdata();
                } catch (Exception $e) {
                    $error = true;
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

                $logModel->setEndTime(date('Y-m-d H:i:s'))
                    ->setStatus($result)
                    ->setDescription('Sync Producer End')
                    ->save();
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

    public function reindexdata()
    {
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $Indexer = $objectManager->create('Magento\Indexer\Model\Processor');
          $Indexer->reindexAll();
    }
}