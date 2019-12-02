<?php
/**
* Qdos Sync
*
* @package          Qdos
* @subpackage       Sync
* @author           Shailendra Gupta
* @copyright        Copyright (c) 2013 - 2014
* @since            Version 1.0
* @purpose          Mass And all Products Qty update
**/

namespace Qdos\QdosSync\Helper\Product;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ResourceConnection;

set_time_limit(0);
ini_set('max_execution_time', 300000);
ini_set('memory_limit', '2048M');
ini_set('default_socket_timeout', 2000);

class Position extends \Qdos\QdosSync\Helper\Data
{

    protected $_messageManager;
    protected $_productRepository;
    protected $_categoryRepository;

    public function __construct(Category $categoryRepository, Product $productRepository, ResourceConnection $resourceConnection, ManagerInterface $messageManager, \Qdos\QdosSync\Model\logFactory $logFactory, \Magento\Framework\App\Filesystem\DirectoryList $directory_list)
    {
        $this->_categoryRepository = $categoryRepository;
        $this->_productRepository = $productRepository;
        $this->_messageManager = $messageManager;
        $this->_getConnection = $resourceConnection->getConnection();
        $this->_categoryProductTable = $resourceConnection->getTableName('catalog_category_product');
        $this->_log = $logFactory;
        $this->directory_list = $directory_list;
    }

    /**
     * bulk price update
     * gets data from service and creates CSV before importing data
     */
    public function syncPosition($productId = null, $categoryId = null, $storeId = 0)
    {
        $base = $this->directory_list->getPath('lib_internal');
        $lib_file = $base . '/Test.php';
        require_once($lib_file);
        $client = Test();

        $productId = is_null($productId)?0:$productId;
        $categoryId = is_null($categoryId)?0:$categoryId;

        $logFileName = "qdos-sync-position-".date('Ymd').".log";
        $logModel = $this->_log->create();
        $start_time = date('Y-m-d H:i:s');
        $logMsgs = $logMsg = $productLogIds = $hiddenProductArr = array();
        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }else{
            $ipAddress = '';
        }           
        $logModel->setActivityType('position')
            ->setStartTime($start_time)
            ->setStatus(\Neo\Winery\Model\Activity::LOG_PENDING)
            ->setIpAddress($ipAddress)
            ->save();
        $logMsgs[] = "Product Position Update Initiated.";
        $client->setLog("Product Position Initiated.",null,$logFileName, true);

        $collection = $this->getMultipleDataFromService($productId, $categoryId, $storeId);
        $client->setLog("Count = ". count($collection),null,$logFileName, true);
        if(is_array($collection) and count($collection)){
            $i = 0;
            foreach ($collection as $data) {
/*                if (!in_array($data['product_id'], $this->_productLogIds)) {
                    $productLogIds[] = $data['product_id'];
                }*/
                $client->setLog("position = ". $data['product_id'].':'.$data['category_product_order'],null,$logFileName, true);
                try {
                    $this->updatePositionProduct($data['category_id'], $data['product_id'], $data['category_product_order']);
                    $logMsgs[] = "Update category #{$data['category_id']} (#{$data['product_id']}: {$data['category_product_order']})";
                    $i++;
                }catch (Exception $e) {
                    $result = \Neo\Winery\Model\Activity::LOG_WARNING;
                    $logMsgs[] = 'Error in processing';
                    $logMsgs[] = $this->addError("Update category #{$data['category_id']} (#{$data['product_id']}: {$data['category_product_order']})".' '
                                       .$e->getMessage());
                }
            }
            $message = 'success';
            $client->setLog("position updated ",null,$logFileName, true);
        }else{
            $status = '1';//$logModel::LOG_SUCCESS;
            $message = $logMsgs[] = "No Records Found.";
            $client->setLog($message,null,$logFileName, true);
        }
        $status = '1';//$logModel::LOG_SUCCESS;
        if (in_array('Error in processing', $logMsgs)) {
            $status = '0';//$logModel::LOG_FAIL;
            //Mage::helper('sync/sync')->sendMailForSyncFailed('Position');
        }
        $client->setLog("Product Position Finished",null,$logFileName,true);
        $logModel->setDescription(implode('<br />', $logMsgs))
                    ->setEndTime(date('Y-m-d H:i:s'))
                    ->setStatus($status)
                    ->save();
        return $message;
    }

    /**
     * Fetching Multiple records from webservice
     * @param $productIds
     * @return array
     */
    protected function getMultipleDataFromService($productIds, $categoryId, $storeId)
    {
        try{
            //print_r($productIds);exit; 
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');

            $base = $this->directory_list->getPath('lib_internal');
            $lib_file = $base . '/Test.php';
            require_once($lib_file);
            $client = Test();

            $clientnew = $client->getConnect($storeId);

            $ids_string = $productIds;
            if(count($productIds) > 0 && is_array($productIds)){
                $ids_string = implode('|', $productIds);
            }
            $resultClient =  $clientnew->GetCategoryProductOrderCSV(array('store_url' => $store_url, 'category_id' => $categoryId, 'product_id' => 0, 'product_id_list'=>$ids_string));

            $collection = array();
            $productData = array();
            if ($resultClient->outErrorMsg && strlen($resultClient->outErrorMsg) > 0) {
                //Mage::throwException('SOAP LOGIN ERROR: ' . $resultClient->outErrorMsg);
            }else{
                if($resultClient->GetCategoryProductOrderCSVResult){
                    $result = $resultClient->GetCategoryProductOrderCSVResult;
                    if (is_object($result) && isset($result->CategoryProductOrderCSV)) {
                        $collection = $result->CategoryProductOrderCSV;
                        if(count($productIds)){
                            $productData = $this->convertObjectsToArray($collection);                        
                        }
                    }
                }
            }

            return $productData;
        }catch (Exception $e){
            $client->setLog($e->getMessage(), 2, "Position_update.log", true);
        }
    }

    private function convertItemToArray($object){
        $new = array();
        if(is_object($object)){
            $new = array_change_key_case((array)$object, CASE_LOWER);
        }
        if(is_array($object)) {
            return $object;
        }
        return $new;
    }

    protected function unsetfiled(&$data)
    {
        unset($data['return_id']);
    }

    public function convertObjectsToArray($objs){
        $items = array();
        if(!is_array($objs))
            $items[] = $this->convertObjectToArray($objs);
        else
            foreach($objs as $obj){
                $items[] =  $this->convertObjectToArray($obj);
            }

        return $items;
    }


    public function updatePositionProduct($categoryId, $productId, $position, $store = null){

        $adapter = $this->_getConnection;
        $category = $this->_categoryRepository
            ->setStoreId($this->_getStoreId($store))
            ->load($categoryId);

        if (!$category->getId()) {
            //Mage::throwException('category not exists');
        }
        $where = array(
            'category_id = ?' => (int)$categoryId,
            'product_id = ?' => (int)$productId
        );
        $bind = array('position' => (int)$position);
        $adapter->update($this->_categoryProductTable, $bind, $where);
    }

    protected function _getStoreId($store = null)
    {
        $storeId = 0;
        return $storeId;
    }
}
