<?php
/**
 * Copyright © 2015 Qdos . All rights reserved.
 */

namespace Qdos\QdosSync\Helper;
//use Magento\Framework\App\Filesystem\DirectoryList
//use Magento\Eav\Setup\EavSetupFactory;
//use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\AttributeManagement;
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Eav\Model\Entity\Attribute\GroupFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\Api\Filter;

class Product extends \Qdos\QdosSync\Helper\Data
{
    const MAX_QTY_VALUE = 99999999.9999;
    const XML_PRODUCTS_PER_SCHEDULE = 'qdossync_url/batch_import/product_per_batch';
    const XML_PRODUCTS_BATCH_SYNC = 'qdossync_url/batch_import/min_product';
    const LOG_SUCCESS = 1;

    protected $_productModel;
    protected $_inventoryFields = array();
    protected $_imageFields = array();
    protected $_systemFields = array();
    protected $_internalFields = array();
    protected $_externalFields = array();
    protected $_allowSystemAttributeArr = array();

    protected $_inventoryItems = array();
    protected $_attributes = array();
    protected $_allowAttributes = array();
    protected $_attributeText = array();
    protected $_attributeHelper;
    protected $_pricingHelper;
    protected $_imageHelper;
    protected $_refFieldArr = array();
    protected $_configHelper;
    protected $_erpHelper;
    protected $_batchExport;
    protected $_batchImport;
    protected $_productTypeInstances = array();
    protected $_recordPerSync = 100;
    protected $_defaultRecordPerSync = 1000;
    protected $_logMsg = array();
    protected $_client = null;
    protected $_tmpData = array();
    protected $_collection;
    protected $_paramsService = array();
    protected $_productLogIds = array();
    protected $_productId = null;
    protected $_queueList = array();
    protected $_logFile = '';
    protected $_stockItem;
    protected $_productFactory;
    protected $_product;
    protected $_productInventory;
    protected $_productAction;

    public function __construct(\Magento\Framework\App\Helper\Context $context,
                                \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
                                AttributeFactory $attributeFactory,
                                SetFactory $attributeSetFactory,
                                GroupFactory $attributeGroupFactory,
                                TypeFactory $typeFactory,
                                AttributeManagement $attributeManagement,
                                \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
                                \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $tableFactory,
                                \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
                                \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory,
                                \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
                                \Qdos\QdosSync\Model\Log $log,
                                \Neo\Winery\Model\Syncgrapes $synccategorieslog,
                                \Qdos\QdosSync\Model\Syncattribute $syncattributelog,
                                \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $stockItem,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Catalog\Model\Product $product,
                                \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        // \Qdos\QdosSync\Helper\product_inventory $product_inventory,
                                array $data = [])
    {
        $this->directory_list = $directory_list;
        $this->attributeFactory = $attributeFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavTypeFactory = $typeFactory;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->attributeManagement = $attributeManagement;
        $this->attributeRepository = $attributeRepository;
        $this->tableFactory = $tableFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
        $this->_log = $log;
        $this->_synccategorieslog = $synccategorieslog;
        $this->_syncattributelog = $syncattributelog;
        $this->_stockItem = $stockItem;
        $this->_productFactory = $productFactory;
        $this->_product = $product;
        $this->_productAction = $productAction;
        // $this->_productInventory = $product_inventory;
        parent::__construct($context, $directory_list, $attributeFactory, $attributeSetFactory, $attributeGroupFactory,
            $typeFactory, $attributeManagement, $attributeRepository,
            $tableFactory, $attributeOptionManagement, $optionLabelFactory, $optionFactory, $log, $synccategorieslog,
            $syncattributelog);
    }

    //GetQdosDeleteProducts
    public function deleteProducts($storeId = 0)
    {
        $status = \Neo\Winery\Model\Activity::LOG_SUCCESS;
        try {
            $base = $this->directory_list->getPath('lib_internal');
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $lib_file = $base . '/Test.php';
            require_once($lib_file);
            $client = Test();

            if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
                $ipAddress = $_SERVER['REMOTE_ADDR'];
            } else {
                $ipAddress = '';
            }
            $start_time = date('Y-m-d H:i:s');
            $logModel = $this->_log;
            $logModel->setActivityType('delete_product')
                ->setStartTime($start_time)
                ->setStoreId($storeId)
                ->setStatus(\Neo\Winery\Model\Activity::LOG_PENDING)
                ->setIpAddress($ipAddress)
                ->save();

            $logFileName = "deleteProducts-" . date('Ymd') . ".log";
            $client->setLog("Delete product ", null, $logFileName);
            $allCategories = array();
            $resultClient = $client->getConnect($storeId);
            $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $resultClient = $resultClient->GetQdosDeleteProducts(array('STORE_URL' => $store_url));

            //echo "<pre>";print_r($resultClient);exit;

            $result = $resultClient->GetQdosDeleteProductsResult;
            $i = 0;
            $message = "success";
            if (is_object($result) && isset($result->ProductDelete)) {
                $productIds = $this->convertObjToArray($result->ProductDelete);

                // $this->log('BEGIN DELETE: '.count($productIds).' product(s)');
                // $this->_logMsg[] = 'BEGIN DELETE: '.count($productIds).' product(s)';

                //Mage::helper('qdossync/cache')->refreshCache();
                $listProductIds = $deletedProductIds = array();
                foreach ($productIds as $product) {
                    if (isset($product->PRODUCT_ID) and is_numeric($product->PRODUCT_ID)) {
                        $listProductIds[] = $product->PRODUCT_ID;
                        //$listProductIds[$product->SKU] = $product->PRODUCT_ID;

                    }
                }
            }

            $stockItem = $this->_stockItem;
            $stockItem->setProcessIndexEvents(false);
            $product = $this->_productFactory->create();
            //$products = $product->loadByAttribute('sku', $listProductIds);
            $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');

            $searchCriteria = $objectManager->get("\Magento\Framework\Api\SearchCriteriaBuilder")
                ->addFilter(
                    'entity_id',
                    $listProductIds,
                    'in'
                )->create();

            $products = $productRepository->getList($searchCriteria)->getItems();
            //$client->setLog(count($products), null, $logFileName);
            //Mage::getResourceModel('catalog/product')->getProductsSku($listProductIds);
            if (count($products)) {
                foreach ($products as $product) {
                    if (strlen($product['sku'])) {
                        try {

                            $client->setLog(json_encode($product), null, $logFileName);
                            $deletedProductIds[] = $product['entity_id'];
                            $stockItem->setData(array());
                            $stockItem->loadByProduct($product['entity_id']);
                            if ($stockItem->getId()) {
                                $stockItem->setProductId($product['entity_id']);
                            } else {
                                $product = $this->_product->load($product['entity_id']);
                                // $this->_productInventory->_prepareItemForSave($stockItem, $product);
                            }

                            $stockItem->setData('use_config_backorders', 0);
                            $stockItem->setData('use_config_manage_stock', 0);
                            $stockItem->setData('qty', 0);
                            $stockItem->setData('is_in_stock', 0);
                            $stockItem->setData('manage_stock', 1);
                            $stockItem->setData('backorders', 0);
                            $stockItem->save();

                            $i++;
                            //$this->_logMsg[] = 'Changed stock: #'.$product['entity_id'];
                            $this->log('Changed stock: #' . $product['entity_id']);
                        } catch (Exception $e) {
                            $message = "fail";
                            $status = \Neo\Winery\Model\Activity::LOG_FAIL;
                            $this->log($e->getMessage());
                            //$this->_logMsg[] = $this->addError('Error when change product #'.$product['entity_id'].': '.$e->getMessage());
                        }
                    }
                }
            }
//
//                    Mage::getSingleton('index/indexer')->indexEvents(
//                        Mage_CatalogInventory_Model_Stock_Item::ENTITY,
//                        Mage_Index_Model_Event::TYPE_SAVE
//                    );
//
            $skipProductIds = array_diff($listProductIds, $deletedProductIds);
//                    foreach ($skipProductIds as $productId){
            //$status = \Neo\Winery\Model\Activity::LOG_WARNING;
//                        $this->_logMsg[] = $this->addWarning('#'.$productId.' is not existed.');
//                    }
//
//                    //disable product
//                    $status = (int)Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
            $updateAttributes['status'] = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
//
            try {
                $this->_productAction->updateAttributes($deletedProductIds, $updateAttributes, 0);

                // Mage::getSingleton('catalog/product_action')
                //         ->updateAttributes($deletedProductIds, array('status' => $status), 0);
                $this->_logMsg[] = __('Total of %d record(s) have been updated.', count($deletedProductIds));
            } catch (Mage_Core_Model_Exception $e) {
                $status = \Neo\Winery\Model\Activity::LOG_FAIL;
                $this->_logMsg[] = $this->addError($e->getMessage());
                $message = "fail";
            } catch (Mage_Core_Exception $e) {
                $status = \Neo\Winery\Model\Activity::LOG_FAIL;
                $this->_logMsg[] = $this->addError($e->getMessage());
                $message = "fail";
            } catch (Exception $e) {
                $message = "fail";
                $status = \Neo\Winery\Model\Activity::LOG_FAIL;
                $this->_logMsg[] = $this->addError($e->getMessage());//$this->__('An error occurred while updating the product(s) status.');
            }
//            }else{
//                 $this->_logMsg[] = 'BEGIN DELETE: 0 product(s)';
//            }
        } catch (Exception $ex) {
            $status = \Neo\Winery\Model\Activity::LOG_FAIL;
            $message = "fail";
            $this->log('Delete products failed.' . $ex->getMessage());
            $this->_logMsg[] = $this->addError($ex->getMessage());
        }
        $logModel->setDescription(implode('<br />', $this->_logMsg))
            ->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus($status)
            ->save();

        return $message;
    }


}