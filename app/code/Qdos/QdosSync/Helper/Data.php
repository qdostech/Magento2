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
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ScopedProductTierPriceManagementInterface;


// use \Psr\Log\LoggerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const TABLE_TIER_PRICE = 'catalog_product_entity_tier_price';
    protected $_pushArr = array();
    protected $_i = 0;
    protected $_lostDataArr = array();
    protected $eavSetup;
    protected $_result = true;
    protected $log;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */


     /**
     * @var ScopedProductTierPriceManagementInterface
     */
    protected $tierPrice;

    protected $groupFactory;

    /**
     * @var ProductTierPriceInterfaceFactory
     */
    protected $productTierPriceFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
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
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        ScopedProductTierPriceManagementInterface $tierPrice,
        ProductTierPriceInterfaceFactory $productTierPriceFactory,
        \Magento\Customer\Model\GroupFactory $groupFactory

    )
    {
        parent::__construct($context);
        $this->attributeFactory = $attributeFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavTypeFactory = $typeFactory;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->attributeManagement = $attributeManagement;
        $this->directory_list = $directory_list;
        $this->attributeRepository = $attributeRepository;
        $this->tableFactory = $tableFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
        $this->_log = $log;
        $this->_synccategorieslog = $synccategorieslog;
        $this->_syncattributelog = $syncattributelog;
        $this->_categoryFactory = $categoryFactory;
        $this->tierPrice = $tierPrice;
        $this->productTierPriceFactory = $productTierPriceFactory;
        $this->groupFactory = $groupFactory;
    }

    public function toOptionMultiSelectArray()
    {
        return array(array('value' => 'Category', 'label' => 'Category'),
            array('value' => 'Attribute', 'label' => 'Attribute'),
            array('value' => 'Product', 'label' => 'Product'),
            array('value' => 'Stock', 'label' => 'Stock'),
            array('value' => 'Price', 'label' => 'Price'),
            array('value' => 'Delete Product', 'label' => 'Delete Product'),
            array('value' => 'Order', 'label' => 'Order'),
            array('value' => 'Manual Sync Product', 'label' => 'Manual Sync Product'),
            array('value' => 'customer', 'label' => 'Customer Sync'),
            array('value' => 'customer_group', 'label' => 'Sync customer Group')
        );
    }

    public function sendMailForSyncFailed($syncType)
    {
        // Transactional Email Template's ID
        $templateId = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/cron_status/sync_fail_template_id');

        // Set sender information
        //$senderName = Mage::getStoreConfig('trans_email/ident_support/name');
        $senderName = "Customer Support";
        //$senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');
        $senderEmail = "support@qdos.com.au";

        $sender = array('name' => $senderName, 'email' => $senderEmail);

        // Set recepient information
        $recepientName = 'Support';
        $recepientEmail = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/cron_status/sync_fail_email_to');
        //Mage::log('recepientEmail'.$recepientEmail, null, 'timediff.log');
        //Mage::log('recepientName'.$recepientName, null, 'timediff.log');

        // Get Store ID
        $storeId = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();

        // Set variables that can be used in email template
        $vars = array('hours' => $cronStatusCheckInterval, 'synctype' => $syncType);

        //$translate  = Mage::getSingleton('core/translate');

        // Send Transactional Email
        /*Mage::getModel('core/email_template')->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
          $translate->setTranslateInline(true); */
    }


    protected function _convertObjectsToArray($objs)
    {
        $items = array();
        if (!is_array($objs))
            $items[] = $this->_convertObjectToArray($objs);
        else
            foreach ($objs as $obj) {
                $items[] = $this->_convertObjectToArray($obj);
            }

        return $items;
    }

    protected function convertObjToArray($object)
    {
        $new = array();
        if (is_object($object)) {
            $new[] = $object;
        }
        if (is_array($object)) {
            $new = $object;
        }
        return $new;
    }

    public function formatAttributeCode($attributeCode)
    {
        $attributeCode = strtolower($attributeCode);
        $attributeCode = preg_replace('/^[a-z][a-z_0-9]$/', '', $attributeCode);
        return $attributeCode;
    }

    protected function _filterPostData($data)
    {
        if ($data) {
            /** @var $helperCatalog Mage_Catalog_Helper_Data */
            // $helperCatalog = Mage::helper('catalog');
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helperCatalog = $objectManager->create('Magento\Catalog\Helper\Catalog');
            //labels
            if (is_array($data->FRONTEND_LABEL)) {
                foreach ($data->FRONTEND_LABEL as & $value) {
                    if ($value) {
                        $value = $helperCatalog->stripTags($value);
                    }
                }
            }
        }
        return $data;
    }

    public function _getAttributeOption($attributeCode)
    {
        $attrList = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $entityTypeId = $objectManager->create('Magento\Eav\Model\Entity')->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();
        $attribute = $objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->loadByCode($entityTypeId, $attributeCode);
        foreach ($attribute->getSource()->getAllOptions(false) as $option) {
            $attrList[$option['value']] = trim($option['label']);
        }
        return $attrList;
    }

    public function _getAttributeOptionKeyLabel($attributeCode)
    {
        $attrList = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $entityTypeId = $objectManager->create('Magento\Eav\Model\Entity')->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();
        $attribute = $objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->loadByCode($entityTypeId, $attributeCode);
        foreach ($attribute->getSource()->getAllOptions(false) as $option) {
            $attrList[trim($option['label'])] = $option['value'];
        }
        return $attrList;
    }

    public function inAttributeSet($attributeSetId = 0, $attributeCode = '')
    {
        //$attributes = Mage::getModel('catalog/product_attribute_api')->items($attributeSetId);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $attributes = $objectManager->create('Magento\Catalog\Model\Product\AttributeSet\Options')->items($attributeSetId);
        // $attributes = $objectManager->create('Magento\Eav\Api\AttributeSetRepositoryInterface')->items($attributeSetId);
        foreach ($attributes as $_attribute) {
            if (isset($_attribute['code']) && $_attribute['code'] == $attributeCode) {
                return true;
            }
        }
        return false;
    }

    public function syncAttribute($ipAddress = '', $storeId = 0)
    {
        // $this->_logger->info(__METHOD__);
        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base . '/Connection.php';
        require_once($lib_file);
        $client = Test();
        $logFileName = "import-" . date('Ymd') . ".log";
        $client->setLog("Sync Attribute ", null, $logFileName);
        $allCategories = array();
        $resultClient = $client->getConnect($storeId);
        $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $unset = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/import_product_settings/not_sync_attribute_properties', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $unsetProperties = array();
        if (strlen($unset)) {
            $unsetProperties = explode(',', $unset);
        }

        $resultClient = $resultClient->GetAttributesCSV(array('store_url' => $store_url, 'entity_type_id' => 4));
        if ($resultClient->outErrorMsg && strlen($resultClient->outErrorMsg) > 0) {
            $client->setLog($resultClient->outErrorMsg, null, 'qdos-sync-attribute.log', true);
            //Mage::throwException($resultClient->outErrorMsg);
        }

        $error = false;
        $success = 0;
        $fail = 0;
        $start_time = date('Y-m-d H:i:s');
        $this->_log->setStartTime($start_time)
            ->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus(\Neo\Winery\Model\Activity::LOG_PENDING)
            ->setStoreId($storeId)
            ->setIpAddress($ipAddress)
            ->setActivityType('attribute')
            ->save();
        /*get Update Products Only value end*/
        $this->_syncattributelog->setStartTime($start_time)
            ->setFinish(date('Y-m-d H:i:s'))
            ->setStatus(\Qdos\QdosSync\Model\Activity::LOG_PENDING)
            ->setFromip($ipAddress)
            ->setActivity('attribute')
            ->save();
        try {
            $result = $resultClient->GetAttributesCSVResult;
            if (is_object($result) && isset($result->AttibutesCSV)) {
                $collection = $this->convertObjToArray($result->AttibutesCSV);
                $client->setLog($collection, null, 'qdos-sync-attribute.log', true);
                if (is_array($collection) && count($collection) > 0) {
                    $logMsg[] = 'Attribute import starts';
                    foreach ($collection as $item) {
                        try {
                            unset($item->ATTRIBUTE_ID);
                            $item->ATTRIBUTE_CODE = $this->formatAttributeCode($item->ATTRIBUTE_CODE);
                            $model = $objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');

                            //validate frontend_input
                            if (isset($item->FRONTEND_INPUT)) {
                                //$validatorInputType = Mage::getModel('eav/adminhtml_system_config_source_inputtype_validator');
                                $validatorInputType = $objectManager->create('Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator');
                                if (!$validatorInputType->isValid($item->FRONTEND_INPUT)) {
                                    foreach ($validatorInputType->getMessages() as $message) {
                                        $client->setLog($message, null, 'qdos-sync-attribute.log', true);
                                    }
                                    continue;
                                }
                            }

                            $item->IS_USER_DEFINED = 1;

                            if (!isset($item->IS_CONFIGURABLE)) {
                                $item->IS_CONFIGURABLE = 0;
                            }
                            if (!isset($item->IS_FILTERABLE)) {
                                $item->IS_FILTERABLE = 0;
                            }

                            if (!isset($item->IS_FILTERABLE_IN_SEARCH)) {
                                $item->IS_FILTERABLE_IN_SEARCH = 0;
                            }

                            $item->BACKEND_TYPE = $model->getBackendTypeByInput($item->FRONTEND_INPUT);
                            $defaultValueField = $model->getDefaultValueByInput($item->FRONTEND_INPUT);

                            /*
                              if ($defaultValueField) {
                                $input->DEFAULT_VALUE = 0;
                            }*/

                            $entityTypeId = $objectManager->create('Magento\Eav\Model\Entity')->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId(); // o/p : 4
                            $attribute = $objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->loadByCode($entityTypeId, $item->ATTRIBUTE_CODE);

                            // attributecode :solid_colour
                            if (!empty($attribute) && $attribute->getId()) {
                                $model->load($attribute->getId());
                                if ($model->getIsUserDefined() < 1) {
                                    continue;
                                }

                                if ($model->getEntityTypeId() != $entityTypeId) {
                                    $client->setLog('This attribute cannot be updated.', null, 'qdos-sync-attribute.log', true);
                                    continue;
                                }
                                unset($item->ENTITY_TYPE_ID);
                            }

                            $applyTo = array();
                            if (isset($item->APPLY_TO) && strlen($item->APPLY_TO) > 0) {
                                if ($item->APPLY_TO != 'all') {
                                    $applyTo = explode(',', $item->APPLY_TO);
                                    $applyToArr = array();
                                    if (is_array($applyTo) && count($applyTo) > 0) {
                                        foreach ($applyTo as $productType) {
                                            $applyToArr[] = trim(strtolower($productType));
                                        }
                                    }
                                    $applyTo = $applyToArr;
                                }
                            }

                            $item->APPLY_TO = $applyTo;
                            $item = $this->_filterPostData($item);

                            //unset frontend properties
                            foreach ($unsetProperties as $properties) {
                                $properties = strtoupper($properties);
                                unset($item->$properties);
                            }

                            $item = (array)$item;
                            $item = (array_change_key_case($item, CASE_LOWER));
                            unset($item['attribute_id']);
                            $model->addData($item);

                            //save option
                            if (in_array($item['frontend_input'], array('multiselect', 'select'))) {
                                $options = explode('|', $item['attribute_value']);
                                $oldOptions = $_oldOptions = array();
                                if ($model->getId()) {
                                    $oldOptions = $this->_getAttributeOption($model->getAttributeCode());
                                    $_oldOptions = $this->_getAttributeOptionKeyLabel($model->getAttributeCode());
                                }
                                $_optionArr = array('value' => array(), 'order' => array(), 'delete' => array());
                                $i = 0;

                                //add new option
                                foreach ($options as $option) {
                                    if (!in_array(trim($option), $oldOptions) && strlen(trim($option))) {
                                        $_optionArr['value']['option_' . $i++] = array($option);
                                    }
                                }

                                //remove option
                                foreach ($oldOptions as $k => $option) {
                                    if (!in_array($option, $options)) {
                                        $_optionArr['delete'][$k] = true;
                                        $_optionArr['value'][$k] = true;
                                    }

                                    //remove duplicate option (additional)
                                    if (!in_array($k, $_oldOptions)) {
                                        $_optionArr['delete'][$k] = true;
                                        $_optionArr['value'][$k] = true;
                                    }
                                }

                                if (count($_optionArr['value']) > 0) {
                                    $model->setOption($_optionArr);
                                }
                            }

                            try {
                                $model->save();
                                $success = 1;
                                $logMsg[] = 'Attribute imported ' . $item['attribute_code'];
                                // echo "saved ".$item['attribute_code']."<br/>";

                                //set all attribute to deafult attribute set - product-details group
                                $this->addAttributeToAllAttributeSets($model->getAttributeCode(), 'product-details');
                            } catch (Exception $e) {
                                $this->_result = false;
                                $fail = 1;
                                $logMsg[] = '<strong style="color:red">Error save attribute "' . $item->ATTRIBUTE_CODE . '": ' . $e->getMessage() . '</strong>';
                                $client->setLog('Error save: ' . $e->getMessage(), null, 'qdos-sync-attribute.log', true);
                                $error = true;
                            }
                        } catch (Exception $e) {
                            $this->_result = false;
                            $fail = 1;
                            $client->setLog('Error:' . $item->ATTRIBUTE_CODE . ' : ' . $e->getMessage(), null, 'qdos-sync-attribute.log', true);
                            $error = true;
                            $logMsg[] = '<strong style="color:red">Error save attribute "' . $item->ATTRIBUTE_CODE . '": ' . $e->getMessage() . '</strong>';
                        }
                    }
                } else {
                    $fail = 1;
                    $client->setLog('Empty data.', null, 'qdos-sync-attribute.log', true);
                    $logMsg[] = 'Empty data.';
                    $client->throwError('Empty data.');
                }
            } else {
                $client->setLog('Empty data.', null, 'qdos-sync-attribute.log', true);
                $fail = 1;
                $logMsg[] = 'Empty data.';
                $client->throwError('Empty data.');
            }
        } catch (Exception $e) {
            $client->throwError($e->getMessage());
            $fail = 1;
            $logMsg[] = 'Error-' . $e->getMessage();
        }

        $logMsg[] = 'Import attribute end';
        if ($fail == '1' && $success == '1') {
            $this->_result = false;
            $result = \Qdos\QdosSync\Model\Activity::LOG_PARTIAL;
        } elseif ($fail == '1') {
            $this->_result = false;
            $result = \Qdos\QdosSync\Model\Activity::LOG_FAIL;
        } elseif ($success == '1') {
            $result = \Qdos\QdosSync\Model\Activity::LOG_SUCCESS;
        } else {
            $result = \Qdos\QdosSync\Model\Activity::LOG_SUCCESS;
        }

        $this->_log->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus($result)
            ->setDescription(implode('<br />', $logMsg))
            ->save();

        return $this->_result;
    }

    /**
     * @param string $attributeCode
     * @param string $attributeGroupCode
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addAttributeToAllAttributeSets(string $attributeCode, string $attributeGroupCode)
    {
        /** @var Attribute $attribute */
        $entityType = $this->eavTypeFactory->create()->loadByCode('catalog_product');
        $attribute = $this->attributeFactory->create()->loadByCode($entityType->getId(), $attributeCode);

        if (!$attribute->getId()) {
            return false;
        }

        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $setCollection */
        $setCollection = $this->attributeSetFactory->create()->getCollection();
        $setCollection->addFieldToFilter('entity_type_id', $entityType->getId());

        /** @var Set $attributeSet */
        foreach ($setCollection as $attributeSet) {
            /** @var Group $group */
            $group = $this->attributeGroupFactory->create()->getCollection()
                ->addFieldToFilter('attribute_group_code', ['eq' => $attributeGroupCode])
                ->addFieldToFilter('attribute_set_id', ['eq' => $attributeSet->getId()])
                ->getFirstItem();

            $groupId = $group->getId() ?: $attributeSet->getDefaultGroupId();

            // Assign:
            $this->attributeManagement->assign(
                'catalog_product',
                $attributeSet->getId(),
                $groupId,
                $attributeCode,
                $attributeSet->getCollection()->count() * 10
            );
        }
        return true;
    }

    /**
     * ProductsSync from here
     *
     * @access    public
     * @params    null
     * @return    void
     **/
    //function to sync the simple products
    private function ProductsSync($filePath, $syncPermissions, $count = 1, $product_id = null, $ipAddress = '', $storeId = 0)
    {
        ini_set('default_socket_timeout', 900); // or whatever new value you want
        $logFileName = "syncProduct-" . date('Ymd') . ".log";

        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base . '/Connection.php';
        require_once($lib_file);
        $client = Test();

        /*get Update Products Only value start*/
        if (!isset($syncPermissions['update_products_only'])) {
            $syncPermissions['update_products_only'] = false;
        }

        if (!isset($syncPermissions['reimport_images'])) {
            $syncPermissions['reimport_images'] = false;
        }

        $start_time = date('Y-m-d H:i:s');
        /*get Update Products Only value end*/
        $this->_log->setStartTime($start_time)
            ->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus(\Qdos\QdosSync\Model\Activity::LOG_PENDING)
            ->setIpAddress($ipAddress)
            ->setStoreId($storeId)
            ->setActivityType('product')
            ->save();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $success = 0;
        $fail = 0;
        $logMsg = array();
        try {
            $importHandler = $objectManager->create('CommerceExtensions\ProductImportExport\Model\Data\CsvImportHandler');
            //$client->setLog("readCsvFile Started. ", null, $logFileName);
            //$logMsg[] = "readCsvFile Started.";
            if ($count > 0) {
                $logMsg = $importHandler->readCsvFile($filePath, $syncPermissions, $client, $logMsg);
            }
            //$client->setLog("readCsvFile end. ", null, $logFileName);
            //$logMsg[] = "readCsvFile end.";
            //$client->setLog("The Products have been imported Successfully.", null, $logFileName);
            //$logMsg[] = "The Products have been imported Successfully.";
            $success = 1;
        } catch (Exception $e) {
            $client->setLog("error msg-" . $e->getMessage(), null, $logFileName);
            $logMsg[] = "Product SKU : " . $importData['sku'] . " imported failed for the reason -" . $e->getMessage();
            $client->setLog("Product SKU : " . $importData['sku'] . " imported failed for the following reason. ", null, $logFileName);
            $fail = 1;
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

        //$client->setLog("Result: " . $result . ' & ' . $success, null, $logFileName);
        $this->_log->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus($result)
            ->setDescription(implode('<br />', $logMsg))
            ->save();

        // Product manual sync
        if ($count > 0 && $product_id != null) {
            $store = $objectManager->get("\Magento\Store\Model\StoreManagerInterface")->getStore();
            $storeId = $store->getStoreId();
            $product = $objectManager->get("\Magento\Catalog\Model\Product")->load($product_id);
            $product->setLastLogId($this->_log->getId());
            //$product->setLastSync(date('Y-m-d H:i:s'));
            if ($fail == '1' && $success == '1') {
                $product->setSyncStatus(\Qdos\QdosSync\Model\Activity::LOG_PARTIAL);
            } elseif ($fail == '1') {
                $product->setSyncStatus(\Qdos\QdosSync\Model\Activity::LOG_FAIL);
            } elseif ($success == '1') {
                $product->setSyncStatus(\Qdos\QdosSync\Model\Activity::LOG_SUCCESS);
            } else {
                $product->setSyncStatus(\Qdos\QdosSync\Model\Activity::LOG_SUCCESS);
            }
            $product->save();
        }
        return $logMsg;
    }

    public function reindexdata()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $Indexer = $objectManager->create('Magento\Indexer\Model\Processor');
        $Indexer->reindexAll();
    }

    /**
     * returns all date attribute
     * @return array
     */
    protected function getDateAttributes()
    {
        $DateAttribute = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $attributes = $objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->getCollection()->addFieldToFilter("frontend_input", "date")->getItems();
        if (count($attributes)) {
            foreach ($attributes as $attribute) {
                $DateAttribute[$attribute->getAttributeCode()] = $attribute->getAttributeCode();
            }
        }
        return $DateAttribute;
    }

    private function convertItemToArray($object)
    {
        $new = array();
        if (is_object($object)) {
            $new = array_change_key_case((array)$object, CASE_LOWER);
        }
        if (is_array($object)) {
            return $object;
        }
        return $new;
    }

    private function formatDescription($description)
    {
        if (preg_match("/<body>/i", $description, $match)) {
            $newDescription = preg_split("/<body>/i", $description);
            $newDescription = preg_split("/<\/body>/i", $newDescription[1]);
            return $newDescription[0];
        }
        return $description;
    }

    public function convertObjectsToArray($objs)
    {
        $items = array();
        if (!is_array($objs))
            $items[] = $this->convertObjectToArray($objs);
        else
            foreach ($objs as $obj) {
                $items[] = $this->convertObjectToArray($obj);
            }

        return $items;
    }

    public function convertObjectToArray($obj)
    {
        $obj = get_object_vars($obj);
        $result = array();
        foreach ($obj as $key => $value) {
            $result[strtolower($key)] = $value;
        }
        return $result;
    }

    public function syncCategory($ipAddress = '', $storeId = 0)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $base = $this->directory_list->getPath('lib_internal');
        $lib_file = $base . '/Connection.php';
        require_once($lib_file);
        $client = Test();
        $logFileName = "syncCategory-" . date('Ymd') . ".log";
        $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');
        try {
            $client->setLog("Sync Category Started ", null, $logFileName);
            $allCategories = array();
            $resultClient = $client->getConnect($storeId);
            $storeMapping = $objectManager->create('Qdos\QdosSync\Model\ResourceModel\Storemapping\Collection')->load()->getData();
            foreach ($storeMapping as $value) {
                if ($value['store_id'] != 0) {
                    $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                } else {
                    $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');
                }
            }

            $success = 0;
            $fail = 0;
            $start_time = date('Y-m-d H:i:s');
            $this->_log->setStartTime($start_time)
                ->setEndTime(date('Y-m-d H:i:s'))
                ->setStatus(\Qdos\QdosSync\Model\Activity::LOG_PENDING)
                ->setIpAddress($ipAddress)
                ->setStoreId($storeId)
                ->setActivityType('category')
                ->save();

            $this->_synccategorieslog->setStart($start_time)
                ->setFinish(date('Y-m-d H:i:s'))
                ->setStatus(\Qdos\QdosSync\Model\Activity::LOG_PENDING)
                ->setIpAddress($ipAddress)
                ->setActivity('category')
                ->save();

            $logMsg[] = "Sync categories starts";
            $allCat = $resultClient->GetCategoriesCSV(array('store_url' => $store_url));

            $collectionData = $allCat->GetCategoriesCSVResult->CategoryCSV;
            $client->setLog("Sync categories before function call", null, "syncCategory-" . date('Ymd') . ".log");
            $storeId = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getStoreId();

            $logMsg = $this->sync($collectionData, $client, $logMsg);
            $client->setLog("Sync categories after function call", null, "syncCategory-" . date('Ymd') . ".log");
            $error = array();
            if (count($this->_lostDataArr) > 0) {
                $this->_result = false;
                $error[] = 'Error:' . count($this->_lostDataArr);
                foreach ($this->_lostDataArr as $data) {
                    // $error[] = '<b style="color:red">Error: '.$data['name'].'('.$data['id'].')</b>'; //pradeep commented
                }
            }
            $this->reindexdata();
            $this->_deleteAllCategories($storeId,$this->_pushArr,$client);
            $client->setLog($error, null, "syncCategory-" . date('Ymd') . ".log");
            $client->setLog("Sync categories end", null, "syncCategory-" . date('Ymd') . ".log");
            $logMsg[] = "Sync categories end";
            $success = 1;
        } catch (Exception $e) {
            $fail = 1;
            $logMsg[] = 'Error in processing';
            $logMsg[] = $this->decodeErrorMsg($e->getMessage());
            $message = $e->getMessage();
            $client->setLog("Sync Categories failed" . print_r($e->getMessage(), true), null, "syncCategory-" . date('Ymd') . ".log");
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

        $this->_log->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus($result)
            ->setDescription(implode('<br />', $logMsg))
            ->save();

        return $this->_result;
    }

    public function sync($collectionData = array(), $client, $logMsg)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeId = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getStoreId();
        $this->_i++;
        $client->setLog('count: ' . $this->_i, null, "syncCategory-" . date('Ymd') . ".log");
        $logMsg[] = 'count: ' . count($collectionData);
        if (is_array($collectionData) and count($collectionData) > 0 && $this->_i < 6) {
            $this->_lostDataArr = array();
            foreach ($collectionData as $items) {
                $item = get_object_vars($items);
                if (!$item['PAR_ROW_ID'] || $item['PAR_ROW_ID'] == 0) {
                    if ($storeId) {
                        $item['PAR_ROW_ID'] = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getRootCategoryId();
                    } else {
                        $item['PAR_ROW_ID'] = 2; //\Magento\Catalog\Model\Category::TREE_ROOT_ID;
                    }
                }

                $parentCategory = $objectManager->get('Magento\Catalog\Model\Category')->load((int)$item['PAR_ROW_ID']);
                if ($parentCategory->getId()) {
                    try {
                        $result = $this->_importSingleCategory($item, $storeId, $client);
                        if ($result) {
                            $this->_pushArr[] = $item['ID'];
                            $client->setLog('Success: ' . $item['ID'] . "---" . $item['NAME'], null, "syncCategory-" . date('Ymd') . ".log");
                            $logMsg[] = 'Success: ' . $item['ID'] . "---" . $item['NAME'];
                        } else {
                            $client->setLog('Error: ' . $item['NAME'], null, "syncCategory-" . date('Ymd') . ".log");
                            $logMsg[] = 'Error: ' . $item['NAME'];
                        }
                        // $this->_result = $result && $this->_result;
                    } catch (Exception $e) {
                        // $this->_result = false;
                        $client->setLog('Error: ' . $e->getMessage() . "--" . $item['Id'], null, "syncCategory-" . date('Ymd') . ".log");
                        $logMsg[] = 'Error: ' . $e->getMessage() . "--" . $item['Id'];
                        continue;
                    }
                } else {
                    $this->_lostDataArr[] = $item;
                    // $client->setLog('item lost: '.$item,null,"syncCategory-".date('Ymd').".log"); //pradeep commented
                    //$logMsg[] = 'item lost: '.$item; //pradeep commented
                }
            }
        }
        return $logMsg;
        // $this->sync($this->_lostDataArr,$client);
    }

    protected function _importSingleCategory($data = array(), $storeId = 1, $client)
    {
        try {
            $logFileName = "syncCategory-" . date('Ymd') . ".log";

            if (strlen($data['NAME']) == 0) {
                $client->setLog("Empty name ", null, $logFileName);
                return false;
            }
            $data = array_change_key_case($data);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $category = $objectManager->create('Magento\Catalog\Model\Category');
            $category->setStoreId($storeId);
            $category->load($data['id']);
            $category->addData($data);
            $category->setId($data['id']);
            $parentId = $data['par_row_id'];
            $setPath = str_replace("-", " ", strtolower($data['name']));

            $collection = $this->_categoryFactory->create()
                    ->getCollection()
                    ->addAttributeToFilter('url_key',strtolower(str_replace(str_split("' "),"-",rtrim($setPath))))
                    ->addFieldToFilter('entity_id',array('neq'=>$data['id']))
                    ->getFirstItem();



                if($collection->getId())
                {
                    // print_r($collection->getData());
                    // exit;
                     $category->setUrlKey($setPath . $data['id']);
                     $client->setLog("Not Empty ID setPATH--".strtolower(str_replace(str_split("' "),"-",rtrim($setPath)))."::<br>", null, "categoryUrlIDKey.log");
                }
                else
                {
                      $category->setUrlKey(strtolower(str_replace(str_split("' "),"-",rtrim($setPath))));
                     $client->setLog("Empty ID SETPATH---".strtolower(str_replace(str_split("' "),"-",rtrim($setPath)))."::<br>", null, "categoryUrlKey.log");
                }


            //$category->setUrlKey($setPath . $data['id']);
            $category->setPosition($data['order']);

            // if (!$parentId) {
            //   if ($storeId) {
            //     $parentId = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getRootCategoryId();
            //   }else{
            //     $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            //   }
            // }

            $parent_category = $objectManager->get('Magento\Catalog\Model\Category')
                ->setStoreId($storeId)
                ->load($parentId);

            if (!$category->getId()) {
                $client->setLog("Empty ID ", null, $logFileName);
                return false;
            }
            $category->setParentId((int)$parent_category->getId());
            $category->setPath($parent_category->getPath() . '/' . $category->getId());
            $path = explode('/', $category->getPath());
            $level = count($path);
            $category->setLevel($level - 1);

            unset($path[count($path) - 1]);
            // $categoryResource = Mage::getResourceModel('catalog/category');
            // Mage::getSingleton('core/resource')->getConnection('core_write')->update(
            //     $categoryResource->getEntityTable(),
            //     array('children_count' => new Zend_Db_Expr('children_count+1')),
            //     array('entity_id IN(?)' => $path)
            // );            

            $category->setAttributeSetId($category->getDefaultAttributeSetId());
            $category->setAvailableSortBy(1)
                ->setDefaultSortBy(1)
                ->setIncludeInMenu(1);

            try {
                $validate = $category->validate();
                if ($validate !== true) {
                    foreach ($validate as $code => $error) {
                        if ($error === true) {
                            $client->setLog("Attribute required " . $category->getResource()->getAttribute($code)->getFrontend()->getLabel(), null, $logFileName);
                        } else {
                            $client->setLog($error, null, $logFileName);
                        }
                    }
                }
                $category->save();
                $_new_ids[] = $category->getId();

            } catch (Exception $e) {
                $client->setLog($e->getMessage(), null, $logFileName);
            }
        } catch (Exception $e) {
            $client->setLog($e->getMessage(), null, $logFileName);
        }
        return true;
    }

    protected function _deleteAllCategories($storeId, $importData, $client)
    {
        $logFileName = "deleteAllCategories-" . date('Ymd') . ".log";
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        try {
            $collection = $objectManager->get('Magento\Catalog\Model\Category')->setStoreId($storeId)->getCollection();
            $allRoot = $this->getAllRootCategoryId();
            $rootId = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getRootCategoryId();
            $objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);
            foreach ($collection as $category) {
                if (!in_array($category->getId(), $importData) &&
                    !in_array($category->getId(),$allRoot) &&
                    !in_array($category->getId(), array($rootId, \Magento\Catalog\Model\Category::TREE_ROOT_ID))
                ) {
                    $category->delete();
                    $client->setLog('Deleted Cateogry::'. $category->getId(), null, $logFileName);
                }
            }
        } catch (Exception $e) {
            $client->setLog($e->getMessage(), null, $logFileName);
        }
    }

    protected function getAllRootCategoryId(){
        $rootIds = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $allStoreId = $objectManager->get('\Qdos\Sync\Helper\Config')->getAllStore();
        foreach ($allStoreId as $storeId){
            $rootIds[] = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore($storeId)->getRootCategoryId();
        }
        $rootIds[] = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        return $rootIds;
    }

    public function addSubcategories($parentId, $catId, $catName, $allCatArray, $currentCat)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $store = $storeManager->getStore();
        $storeId = $store->getStoreId();
        $parentCategory = $objectManager->create('Magento\Catalog\Model\Category')->load($parentId);

        if ($parentCategory->getId()) {
            // print_r($currentCat->ID);die;
            // echo "parent id".$parentCategory->getId()."id".$catId."name=".$catName."<br>";
            // die;
            $name = ucfirst($catName);
            $url = str_replace("-", " ", strtolower($catName));
            $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
            $categoryTmp = $categoryFactory->create();
            $categoryTmp->setId($catId);
            $categoryTmp->setName($name);
            $categoryTmp->setIsActive(true);
            $categoryTmp->setIsAnchor(1);
              $collection = $this->_categoryFactory->create()
                    ->getCollection()
                    ->addAttributeToFilter('url_key',strtolower(str_replace(str_split("' "),"-",rtrim($url))))
                    ->addFieldToFilter('entity_id',array('neq'=>$catId))
                    ->getFirstItem();

                 if($collection->getId())
                {
                    
                     $client->setLog("Not Empty ID".strtolower(str_replace(str_split("' "),"-",rtrim($url)))."::<br>", null, "categoryUrlIDKey.log");
                     $categoryTmp->setUrlKey($url . $catId);
                }
                else
                {
                     $client->setLog("Empty ID".strtolower(str_replace(str_split("' "),"-",rtrim($url)))."::<br>", null, "categoryUrlKey.log");    
                    $categoryTmp->setUrlKey(strtolower(str_replace(str_split("' "),"-",rtrim($url))));
                  }


          // $categoryTmp->setUrlKey($url . $catId);
            $categoryTmp->setData('description', 'description');
            $categoryTmp->setParentId($parentCategory->getId());
            $categoryTmp->setStoreId($storeId);
            $path = explode('/', $parentCategory->getPath());
            $level = count($path);
            $categoryTmp->setLevel($level);
            $categoryTmp->setPath($parentCategory->getPath() . '/' . $catId);
            $categoryTmp->save();

            // if($currentCat->ID == $catId){
            //     echo "in if";
            // }else{
            //   echo "here in elsejiiii";die;
            //   $found_key = array_search($currentCat->PAR_ROW_ID, array_column($allCatArray, 'ID'));
            //   if($found_key){
            //     $currentCat = $allCatArray[$currentCat->ID];
            //     $catArray = $allCatArray[$found_key];
            //     $this->addSubcategories($catArray->PAR_ROW_ID,$catArray->ID,$catArray->NAME,$allCatArray,$currentCat);
            //   }else{
            //     $remArr[] = $catId;
            //     return $remArr;
            //   }
            // }

        } else {
            $found_key = array_search($parentCategory->getId(), array_column($allCatArray, 'ID'));
            if ($found_key) {
                $currentCat = $allCatArray[$catId];
                $catArray = $allCatArray[$found_key];
                $this->addSubcategories($catArray->PAR_ROW_ID, $catArray->ID, $catArray->NAME, $allCatArray, $currentCat);
            } else {
                $remArr[] = $catId;
                return $remArr;
            }
        }

        return;
    }

    public function getProductExport($productIds = '', $storeId = 0, $ipAddress = '')
    {
        ini_set('default_socket_timeout', 900); // or whatever new value you want
        ini_set("memory_limit", "2048M");
        passthru("/bin/bash rename.sh");
         $logFileName = "importproduct-" . date('Ymd') . ".log";
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $skuBasedProductSync = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/sku_based_product_sync');
        $manualSyncProduct = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/manual_sync_product');
        $manualSyncPrice = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/manual_sync_price');
        $manualSyncStock = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/manual_sync_stock');
        $manualSyncAttribute = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/manual_sync_attribute');
        $manualDelProduct = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/manual_delete_product');
        $manualProdPosition = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/manual_product_position');
        $fixedPriceConfigChild = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/fixed_price_for_config_child');
        $tierPriceSync = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/tier_price_sync');
        $prodImageSync = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/product_image_sync');
        $manualCatSync = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/manual_category_sync');
        $productImgImportSync = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/product_image_import_sync');
        $delSyncImages = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/delete_qdos_sync_images');
        $resetDelStockSync = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/reset_stock_delete_sync');
        $appendCategories = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/append_categories');

        $syncPermissions = array();
        $filePath = array();

        $syncPermissions['skuBasedProductSync'] = $skuBasedProductSync;
        $syncPermissions['manualSyncProduct'] = $manualSyncProduct;
        $syncPermissions['manualSyncPrice'] = $manualSyncPrice;
        $syncPermissions['manualSyncStock'] = $manualSyncStock;
        $syncPermissions['manualSyncAttribute'] = $manualSyncAttribute;
        $syncPermissions['manualDelProduct'] = $manualDelProduct;
        $syncPermissions['manualProdPosition'] = $manualProdPosition;
        $syncPermissions['fixedPriceConfigChild'] = $fixedPriceConfigChild;
        $syncPermissions['tierPriceSync'] = $tierPriceSync;
        $syncPermissions['prodImageSync'] = $prodImageSync;
        $syncPermissions['manualCatSync'] = $manualCatSync;
        $syncPermissions['productImgImportSync'] = $productImgImportSync;
        $syncPermissions['delSyncImages'] = $delSyncImages;
        $syncPermissions['resetDelStockSync'] = $resetDelStockSync;
        $syncPermissions['appendCategories'] = $appendCategories;

        $filePath['simpleProduct'] = $this->directory_list->getPath('var') . '/import/import_products_group.csv';
        //$this->directory_list->getPath('var'). '/import/import_products_simple.csv';
        //$returnMsgs = $this->ProductsSync($filePath['simpleProduct'], $syncPermissions);

        /*END:Get All permission from configuration

        @author: Pooja Soni
        Date: 5th_may_17
        */
        $logMsgs = $logMsg = $productLogIds = $hiddenProductArr = array();
        $base = $this->directory_list->getPath('lib_internal');
        $lib_file = $base . '/Connection.php';
        require_once($lib_file);
        $client = Test();
        try {
            $resultClient = $client->getConnect($storeId);
            $product_id = 0; // 0 to get all products
            $product_type = ''; // 'All' to get all types of products
            $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');
            $logFileName = "importproduct-" . date('Ymd') . ".log";

            $client->setLog("Getting Products ", null, $logFileName);

            if (strlen($productIds) > 0) {

                $product_id_list = $productIds; // get details of the specific product ids
                $product = $objectManager->get("\Magento\Catalog\Model\Product")->load($product_id_list);

             
                $resultClient = $resultClient->Getproductscsv(array('store_url' => $store_url, 'PRODUCT_ID' => $product_id, 'PRODUCT_TYPE' => $product_type, 'PRODUCT_ID_LIST' => $product_id_list));

                //$client->setLog("nnnnnnnnnnnn" . json_encode($resultClient, true), null, $logFileName);
                $productcsv = $this->directory_list->getPath('var') . '/import/import_products_simple_manual.csv';
                $productcsvother = $this->directory_list->getPath('var') . '/import/import_products_other_manual.csv';
            } else {

                $productcsv = $this->directory_list->getPath('var') . '/import/import_products_simple.csv';
                $productcsvother = $this->directory_list->getPath('var') . '/import/import_products_other.csv';
                $resultClient = $resultClient->Getproductscsv(array('store_url' => $store_url, 'PRODUCT_ID' => $product_id, 'PRODUCT_TYPE' => $product_type));
            }

            if (file_exists($productcsv) && (!is_writable($productcsv) || !is_writable($productcsvother))) {
                $returnMsgs[] = 'Error in processing';
                $returnMsgs[] = "<br/><b> The following CSV files should be writable :- </b><br/>" . $productcsv . "<br/>" . $productcsvother;
                $logMsgs[] = implode('<br />', $returnMsgs);
                $permissionError = TRUE;
                $message = "Not enough permission to create the CSV file.";
            } else {
                $permissionError = FALSE;
                $collection = array();
                $objCollection = array();
                if ($resultClient->outErrorMsg && strlen($resultClient->outErrorMsg) > 0) {
                    $client->throwError($resultClient->outErrorMsg);
                    //throw new \Magento\Exception('SOAP LOGIN ERROR' . $resultClient->outErrorMsg));
                } else {
                    $result = $resultClient->GetProductsCSVResult;
                    if (is_object($result) && isset($result->ProductCSV)) {
                        $objCollection = $result->ProductCSV;
                    }
                }

                // echo count($objCollection); exit;

                $client->setLog("Products count => " . count($objCollection), null, $logFileName);

                /*if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
                  $ipAddress = $_SERVER['REMOTE_ADDR'];
                }else{
                  $ipAddress = '';
                }
                if ($ipAddress == '') {
                  $logMsgs[] = "Cron Sync Process";
                }else{
                  $logMsgs[] = "Manual Sync Process";
                }*/

                $logMsgs[] = "Total Products Count = " . count($objCollection);


               // echo "<pre>";print_r($objCollection);exit;
                $client->setLog("CSV Generation starts", null, $logFileName);
                $headerflag = 0;

                if (!file_exists($this->directory_list->getPath('var') . '/import/')) {
                    mkdir($this->directory_list->getPath('var') . '/import/', 0777, true);
                }
                $file = fopen($productcsv, "w"); // pradeep commented
                $fileother = fopen($productcsvother, "w"); // pradeep commented
                //chmod($file, 0777);
                $csvarray = array();
                $csvotherarray = array();
                $arrUrlKey = array();

                /** @var    \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
                $arrProducts = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')->addAttributeToSelect('url_key');
                $exist = 0;
                foreach ($arrProducts as $product) {
                    $arrUrlKey[$product->getSku()] = $product->getUrlKey(); 
                    //$arrUrlKey[] = $product->getUrlKey(); //get name
                }

                //echo "<pre>";print_r($arrUrlKey);exit;

                if (count($objCollection) == 1) {
                    $collection[] = $objCollection;
                } else {
                    $collection = $objCollection;
                }

                $productSkuMap = array();
                $CodeInColumn = array();
                $arrProductData = array();
                foreach ($collection as $item) {
                    $item = $this->convertItemToArray($item);
                    $productSkuMap[$item['id']] = $item['sku'];
                    $arrProductData[$item['sku']] = $item;

                    /**
                     * ####[Attribute mapping]-Starts####
                     * building array of attributes for columns to be made in Csv
                     *
                     * DM
                     */

                    if (isset($item['attribute_code_value'])) {
                        $attributes = explode("|", $item['attribute_code_value']);
                        foreach ($attributes as $attribute) {
                            $tmp = explode(":", $attribute);
                            if (isset($tmp[0])) {
                                array_push($CodeInColumn, $tmp[0]);
                            }
                        }
                        $CodeInColumn = array_unique($CodeInColumn);
                    }

                    /**
                     * ####[Attribute mapping]-Ends####
                     */
                }

                //end by Shailendra Gupta

                /**
                 * ##Gets All date attribute
                 */

                $DateAttributes = $this->getDateAttributes();

                $attribute_code = "description";
                $attribute_details = $objectManager->create('Magento\Eav\Model\Config')->getAttribute('catalog_product', $attribute_code);
                $descriptionIsRequired = $attribute_details->getIsRequired();

                $attribute_code = "short_description";
                $attribute_details = $objectManager->create('Magento\Eav\Model\Config')->getAttribute('catalog_product', $attribute_code);
                $shortDescriptionIsRequired = $attribute_details->getIsRequired();
                //==================================================================================================================

                $flag = 1;
                foreach ($collection as $item) {
                    $item = $this->convertItemToArray($item);
                    
                    if (!empty($item['url_key'])) {          
                        if (isset($arrUrlKey[$item['sku']]) && $arrUrlKey[$item['sku']] != $item['url_key']) {
                            if(in_array($item['url_key'], $arrUrlKey)){
                                $urlKey = $item['url_key'];
                                if ($urlKey == '') {                                   
                                    $lowerName = rtrim(strtolower($item['name']));
                                    $urlKey = str_replace(' ','-', $lowerName);
                                }
                                if(in_array($urlKey, $arrUrlKey)){
                                    $randomNo = mt_rand(100000, 999999);
                                    $newUrlKey = $urlKey . '-' . $randomNo;
                                    $item['url_key'] = $newUrlKey;
                                }else{
                                    $item['url_key'] = $urlKey;                                    
                                }
                            }

                        }
                        
                    }else{
                        $item['url_key'] = '';
                    }
                    $arrUrlKey[$item['sku']] = $item['url_key'];

                    if (!isset($item['special_price']) || $item['special_price'] == '') {
                        $item['special_price'] = '';
                    }

                    if (isset($item['special_price']) && (float)$item['special_price'] == 0) {
                    $item['special_price'] = '';
                    }

                    //=============================================================================================================
                    /* ----- Bundle Products Script ------- */
                    //added by Shailendra Gupta on 30-April-2014 for adding bundle products in CSV

                    if (strtolower($item['type']) == 'bundle') {
                        $optionRawData = array();
                        $selectionRawData = array();
                        $combineSelectionRawData = array();
                        if (isset($item['bundle_options'])) {
                            $bundle_options = $item['bundle_options'];
                            $bundle_options = $this->convertObjectToArray($bundle_options);
                            if (isset($bundle_options['productbundlecsv'])) {
                                $bundle_options = $this->convertObjectsToArray($bundle_options['productbundlecsv']);
                                foreach ($bundle_options as $option) {
                                    $optionRawData[] = implode(',', array($option['title'], $option['type'], $option['required'], $option['position']));
                                    $pSelections = $this->convertObjectToArray($option['product_selection']);
                                    if (isset($pSelections['productbundleoptionscsv'])) {
                                        $pSelections = $pSelections['productbundleoptionscsv'];
                                        $pSelections = $this->convertObjectsToArray($pSelections);
                                        foreach ($pSelections as $pSelection) {
                                            $selectionRawData[] = implode(':', array($pSelection['sku'], $pSelection['selection_price_type'], $pSelection['selection_price_value'], $pSelection['is_default'], $pSelection['selection_qty'], $pSelection['selection_can_change_qty'], $pSelection['position']));
                                        }
                                    }

                                    $combineSelectionRawData[] = implode(',', $selectionRawData);
                                }
                            }
                        }

                        $item['bundle_options'] = implode('|', $optionRawData);
                        $item['bundle_selections'] = implode('|', $combineSelectionRawData);

                        if (isset($item['special_price'])) {
                            if (($item['price'] > 0) && ($item['special_price'])) {
                                $item['special_price'] = ($item['special_price'] / $item['price']) * 100;
                            }
                        }

                        //Rahul Chavan 
                        //date 5 feb 2020 ...group price changes

                        if (isset($item['groupid_price']) && ($item['price'] > 0)) {
                            $groupPrice = str_replace(':', '=', $item['groupid_price']);
                            $arrGroupPrice = explode("|", $groupPrice);
                            $arrPercentGroupPrice = array();
                            foreach ($arrGroupPrice as $key => $value) {
                                $arrPricing = explode("=", $value);
                                $percentGroupPrice = 100 - (($arrPricing[1] / $item['price']) * 100);
                                $arrPercentGroupPrice[] = $arrPricing[0] . '=' . $percentGroupPrice;
                            }
                            $strPercentGroupPrice = implode("|", $arrPercentGroupPrice);


                              //Check Customer Group Exist validation
                            $customerGroupData = $this->groupFactory->create();
                             $group_price_string = explode("|", $strPercentGroupPrice);
                             $grpprice=array();
                             foreach ($group_price_string as $TpriceDataString) {


                                    $TpriceData = explode("=", $TpriceDataString);

                                    $customer_groupId= $TpriceData[0];

                                    $customerGroupDatas= $customerGroupData->load($customer_groupId); 
                                      
                                        if(count($customerGroupDatas->getData())>0)
                                        {
                                        $grpprice[]=$TpriceDataString;
                                        }
                                        else
                                        {
                                        $client->setLog("Group Price Not added Customer group missing-- ".$item['sku'], null, $logFileName); 
                                        $logMsgs[]="Group Price Not added Customer group missing-- ".$item['sku'];
                                        }
                                                                    
                                }

                               $grpprice=implode('|', $grpprice) ; 


                            $item['group_price_price'] = $grpprice;

                         /*   $groupid_price=$item['groupid_price'];
                                //delete existing group price
                              $productId = $objectManager->get('Magento\Catalog\Model\Product')->getIdBySku($item['sku']);

                                if($productId)
                                {
                                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                    $connection = $resource->getConnection();
                                    $myTable = $resource->getTableName(self::TABLE_TIER_PRICE);

                                    $connection->delete(
                                        $myTable,
                                        ['entity_id = ?' => $productId]
                                    );
                                 }
                             $item['group_price_price']=$item['groupid_price'];*/
                        }

                        else
                        {
                             $productId = $objectManager->get('Magento\Catalog\Model\Product')->getIdBySku($item['sku']);
                            if($productId)
                                {
                                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                    $connection = $resource->getConnection();
                                    $myTable = $resource->getTableName(self::TABLE_TIER_PRICE);
                                     $sqls = " DELETE FROM ".$myTable." WHERE entity_id= ".$productId." AND qty=1 ";
                                         $connection->query($sqls);
                                    // $connection->delete(
                                    //     $myTable,
                                    //     ['entity_id = ?' => $productId]
                                    // );
                                 }


                        }
                        unset($item['groupid_price']);
                    } else {
                        $item['bundle_options'] = '';
                        $item['bundle_selections'] = '';
                        if (isset($item['groupid_price'])) {
                            //Check Customer Group Exist validation
                            $customerGroupData = $this->groupFactory->create();
                             $group_price_string = explode("|", $item['groupid_price']);
                             $grpprice=array();
                             foreach ($group_price_string as $TpriceDataString) {


                                    $TpriceData = explode(":", $TpriceDataString);

                                    $customer_groupId= $TpriceData[0];

                                    $customerGroupDatas= $customerGroupData->load($customer_groupId); 
                                       
                                        if(count($customerGroupDatas->getData())>0)
                                        {
                                        $grpprice[]=$TpriceDataString;
                                        }
                                        else
                                        {
                                        $client->setLog("Group Price Not added Customer group missing-- ".$item['sku'], null, $logFileName); 
                                        $logMsgs[]="Group Price Not added Customer group missing-- ".$item['sku'];
                                        }
                                                                    
                                }

                               $grpprice=implode('|', $grpprice) ; 

                            $groupPrice = str_replace(':', '=', $grpprice);
                            $item['group_price_price'] = $groupPrice;
                        }
                        unset($item['groupid_price']);
                    }

                    //$item['weight_type'] = 1;
                    //end by Shailendra Gupta
                    //=============================================================================================================

                    if (!isset($item['description']) || strlen(trim($item['description'])) == 0 || empty($item['description'])) {
                        if (isset($descriptionIsRequired) && $descriptionIsRequired == 1) {
                            $item['description'] = $item['name'];
                        } else {
                            $item['description'] = '';
                        }
                    }

                    if (!isset($item['short_description']) || strlen(trim($item['short_description'])) == 0 || empty($item['short_description'])) {
                        if (isset($shortDescriptionIsRequired) && $shortDescriptionIsRequired == 1) {
                            $item['short_description'] = $item['name'];
                        } else {
                            $item['short_description'] = '';
                        }
                    }

                    $storeIds = $item['store_id'];
                    unset($item['store_id']);

                    //------------------Configurable Product Changes ----------------------------------------------

                    if (strtolower($item['type']) == 'configurable') {
                        $superAttributePricing = array();
                        $superAttributePricingConfigured = array();
                        $configPrice = $item['price'];
                        $item['associated'] = $item['product_skus'];
                        $item['config_attributes'] = strtolower($item['super_attribute']);
                        $associatedProducts = explode(',', $item['product_skus']);
                        $configAttributes = explode(',', $item['config_attributes']);
                        sort($configAttributes);
                        foreach ($associatedProducts as $akey => $productSku) {
                            $productData = array();
                            if (array_key_exists($productSku, $arrProductData)) {
                                $productData = $arrProductData[$productSku];
                                $productPrice = $productData['price'];
                                $fixedPriceForConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/fixed_price_for_config_child');
                                if ($fixedPriceForConfig) {
                                    $priceDiff = $productPrice - $configPrice;
                                } else {
                                    $priceDiff = 0;
                                }
                                foreach ($configAttributes as $ckey => $configAttr) {
                                    if (!in_array($productData[$configAttr], $superAttributePricingConfigured)) {
                                        $superAttributePricingConfigured[] = $productData[$configAttr];
                                        $superAttributePricing[] = $productData[$configAttr] . ':' . $priceDiff . ':' . '0';
                                    }
                                    break;
                                }
                            }
                        }
                        $item['super_attribute_pricing'] = implode('|', $superAttributePricing);
                    } else {
                        $item['associated'] = '';
                        $item['config_attributes'] = '';
                        $item['super_attribute_pricing'] = '';
                    }

                    $enableTierPriceSync = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/tier_price_sync');
                    if ($enableTierPriceSync) {

                        

                        if ($item['tier_price'] != '') {
                            //Added by Shailendra Gupta on 8 July 2015 for tier price sync
                            if (strtolower($item['type']) == 'bundle') {
                                $tierPrice = str_replace(":", "=", $item['tier_price']);
                                $arrTierPrice = explode("|", $tierPrice);
                                $arrPercentTierPrice = array();
                                foreach ($arrTierPrice as $key => $value) {
                                    $arrPricing = explode("=", $value);
                                    $percentTierPrice = 100 - (($arrPricing[2] / $item['price']) * 100);
                                    $arrPercentTierPrice[] = $arrPricing[0] . '=' . $arrPricing[1] . '=' . $percentTierPrice;
                                }
                                $tierPrice = implode("|", $arrPercentTierPrice);
                            } else {
                                                              
                                     $customerGroupData = $this->groupFactory->create();
                             $tier_price_string = explode("|", $item['tier_price']);
                             $tier_price_str=array();
                             foreach ($tier_price_string as $TpriceDataString) {


                                    $TpriceData = explode(":", $TpriceDataString);

                                    $customer_groupId= $TpriceData[0];

                                    $customerGroupDatas= $customerGroupData->load($customer_groupId); 
                                       
                                        if(count($customerGroupDatas->getData())>0)
                                        {
                                        $tier_price_str[]=$TpriceDataString;
                                        }
                                        else
                                        {
                                        $client->setLog("Tier Price Not added Customer group missing-- ".$item['sku'], null, $logFileName); 
                                        $logMsgs[]="Group Price Not added Customer group missing-- ".$item['sku'];
                                        }
                                                                    
                                }

                               $tier_price_str=implode('|', $tier_price_str) ; 

                                 $tierPrice = str_replace(":", "=", $tier_price_str);
                            }
                            //End by Shailendra Gupta for tier price sync
                            $item['tier_prices'] = $tierPrice; 
                            //$item['tier_prices'] = '0=5=19|1=10=18';
                            //$item['tier_prices'] = '0=4=6.80|1=4=6.80|6=4=6.80';
                        } else {
                            $item['tier_prices'] = '';
                            $productId = $objectManager->get('Magento\Catalog\Model\Product')->getIdBySku($item['sku']);
                            if($productId)
                                {
                                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                    $connection = $resource->getConnection();
                                    $myTable = $resource->getTableName(self::TABLE_TIER_PRICE);
                                     $sqls = " DELETE FROM ".$myTable." WHERE entity_id= ".$productId." AND qty!=1 ";
                                         $connection->query($sqls);
                                    // $connection->delete(
                                    //     $myTable,
                                    //     ['entity_id = ?' => $productId]
                                    // );
                                 }

                        }
                    }

                    unset($item['tier_price']);
                    unset($item['super_attribute']);

                    //------------------Configurable Product Changes ----------------------------------------------
                    //------------------Downloadable Product Changes [Starts here]----------------------------------------------
                    if (strtolower($item['type']) == 'downloadable') {
                        $item["downloadable_options"] = " ";
                        $item["links_title"] = " ";
                        $item["links_purchased_separately"] = " ";
                        if (isset($item["downloadable_data"])) {
                            $download_data = $item["downloadable_data"];
                            unset($item["downloadable_data"]);
                            $item["links_title"] = $download_data["links_title"];
                            $item["links_purchased_separately"] = $download_data["links_purchased_separately"];
                            $download_options = $download_data["downloadable_options"];

                            $formated_option = array();
                            foreach ($download_options as $option) {
                                $option_data = array(
                                    $option["link_name"],
                                    $option["link_price"],
                                    $option["no_of_downloads"],
                                    $option["link_type"],
                                    $option["link"]
                                );
                                $formated_option[] = implode(",", $option_data);
                            }
                            $item["downloadable_options"] = implode("|", $formated_option);
                        }
                    } else {
                        $item["downloadable_options"] = " ";
                        $item["links_title"] = " ";
                        $item["links_purchased_separately"] = " ";

                        if (isset($item["downloadable_data"])) {
                            unset($item["downloadable_data"]);
                        }
                    }

                    //------------------Downloadable Product Changes [Ends here]----------------------------------------------
                    //------------------Grouped Product Changes [Starts here]----------------------------------------------

                    if (strtolower($item['type']) == 'grouped') {
                        $item['grouped'] = " ";
                        if (isset($item['product_skus'])) {
                            $item['grouped'] = $item['product_skus'];
                        }
                    } else {
                        $item['grouped'] = " ";
                    }

                    //------------------Grouped Product Changes [Ends here]----------------------------------------------
                    unset($item['product_skus']);
                    $item['description'] = $this->formatDescription($item['description']);
                    $item['short_description'] = $this->formatDescription($item['short_description']);

                    if (isset($item['cross_sells'])) {
                        $arrIdCrossSells = explode(',', $item['cross_sells']);
                        $arrSkuCrossSells = array();
                        foreach ($arrIdCrossSells as $key => $value) {
                            if (array_key_exists($value, $productSkuMap)) {
                                $arrSkuCrossSells[] = $productSkuMap[$value];
                            }
                        }
                        $item['crosssell'] = implode(',', $arrSkuCrossSells);
                    } else {
                        $item['crosssell'] = '';
                    }

                    if (isset($item['up_sells'])) {
                        $arrIdUpSells = explode(',', $item['up_sells']);
                        $arrSkuUpSells = array();
                        foreach ($arrIdUpSells as $key => $value) {
                            if (array_key_exists($value, $productSkuMap)) {
                                $arrSkuUpSells[] = $productSkuMap[$value];
                            }
                        }
                        $item['upsell'] = implode(',', $arrSkuUpSells);
                    } else {
                        $item['upsell'] = '';
                    }

                    if (isset($item['related_product'])) {
                        $arrIdRelated = explode(',', $item['related_product']);
                        $arrSkuRelated = array();
                        foreach ($arrIdRelated as $key => $value) {
                            if (array_key_exists($value, $productSkuMap)) {
                                $arrSkuRelated[] = $productSkuMap[$value];
                            }
                        }
                        $item['related'] = implode(',', $arrSkuRelated);
                    } else {
                        $item['related'] = '';
                    }

                    if (isset($item['attribute_set'])) {
                        $item['attribute_set'] = 'Default';
                    }

                    if (isset($item['visibility'])) {
                        $visibility = $item['visibility'];
                        $item['visibility'] = str_replace(',', ', ', $visibility);
                    }

                    //$enableTierPriceSync =$objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/product_image_import_sync');
                    if ($objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/product_image_import_sync')) {
                        if (isset($item['product_image_types'])) {
                            $image = array();
                            $smallImage = array();
                            $thumbnail = array();
                            $gallery = array();
                            $swatchImage = array();
                            $excludeImage = array();
                            $arrProductImagesTypes = explode(',', $item['product_image_types']);
                            $arrProductImagesNames = explode(',', $item['product_image_names']);
                            foreach ($arrProductImagesTypes as $key => $imageType) {
                                if ($imageType == 'primary') {
                                    $image[] = '/' . $arrProductImagesNames[$key];
                                } else if ($imageType == 'small_image') {
                                    $smallImage[] = '/' . $arrProductImagesNames[$key];
                                } else if ($imageType == 'thumbnail') {
                                    $thumbnail[] = '/' . $arrProductImagesNames[$key];
                                } else if ($imageType == 'gallery') {
                                    $gallery[] = '/' . $arrProductImagesNames[$key];
                                } else if ($imageType == 'swatch') {
                                    $swatchImage[] = '/' . $arrProductImagesNames[$key];
                                } else if ($imageType == 'exclude') {
                                    $excludeImage[] = '/' . $arrProductImagesNames[$key];
                                }
                            }
                        }

                        //Default
                        $item['image'] = implode(',', $image);
                        $item['small_image'] = implode(',', $smallImage);
                        $item['thumbnail'] = implode(',', $thumbnail);
                        $item['gallery'] = implode(',', $gallery);
                        $item['swatch'] = implode(',', $swatchImage);
                        $item['exclude'] = implode(',', $excludeImage);

                        $item['image_label'] = $item['name'];
                        $item['small_image_label'] = $item['name'];
                        $item['thumbnail_label'] = $item['name'];
                        $item['gallery_label'] = $item['name'];
                        $item['swatch_label'] = $item['name'];
                    }

                    $storeModel = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore($storeId);
                    $websiteData = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getWebsite($storeModel['website_id'])->getData();

                    if ($storeId == 0) {
                        $item['websites'] = 'base';
                    } else {
                        $item['websites'] = $websiteData['code'];
                    }

                    $item['store'] = $storeModel['code'];

                    /**
                     * ####[Attribute mapping]-Starts####
                     * builds array of attribute to be imported
                     * -DM
                     */

                    $CurrentProductAttributes = array();
                    $attr_value = array();
                    if (isset($item['attribute_code_value'])) {
                        $attributes = explode("|", $item['attribute_code_value']);

                        foreach ($attributes as $attribute) {
                            $tmp = explode(":", $attribute);
                            if (isset($tmp[0])) {
                                $CurrentProductAttributes[$tmp[0]] = "";
                                if (isset($tmp[1])) {
                                    $attr_value = $tmp;
                                    unset($attr_value[0]);
                                    $attr_value = implode(":", $attr_value);
                                    $attr_value = str_replace(",", " ,", $attr_value);
                                    $CurrentProductAttributes[$tmp[0]] = $attr_value;
                                }
                            }
                        }
                    }

                    //Mapping column here
                    if (count($CodeInColumn)) {
                        foreach ($CodeInColumn as $columns) {
                            if (isset($CurrentProductAttributes[$columns])) {
                                $item[$columns] = $CurrentProductAttributes[$columns];

                            } else {
                                $item[$columns] = " ";
                                //added for setting blank date fields
                                if (isset($DateAttributes[$columns])) {
                                    $item[$columns] = 0;
                                }

                                if (!in_array($columns, $CurrentProductAttributes)) {
                                    $msg = "Error while creating attribute => " . $columns . " For product code => " . $item['sku'];
                                    // Mage::log($msg, 1, "Missed_attribute.log", true);
                                }
                            }
                        }
                    }

                    /**
                     * ####[Attribute mapping]-Ends####
                     */

                    $syncpermission = $objectManager->create('Qdos\QdosSync\Model\ResourceModel\Storemapping\Collection')->addFieldToFilter('sync_type', 'productsync')->load()->getData();
                    foreach ($syncpermission as $permissionKey => $permissionValue) {
                        if ($permissionValue['sync_status'] == 0) {
                            unset($item[$permissionValue['sync_attribute']]);
                        }
                    }

                    $finalarray = array();
                    array_push($finalarray, $item);

                    if ($headerflag == 0) {
                        fputcsv($file, array_keys($finalarray[0])); //pradeep commented
                        fputcsv($fileother, array_keys($finalarray[0])); //pradeep commented
                        $headerflag = 1;
                    }

                    //pradeep commented
                    if (strtolower($item['type']) == 'simple') {
                        //fixes for shifting url key field on correct position
                        /*if ($flag > 1 && $exist == 1) {
                            $this->moveElement($finalarray[0], 84, 14);
                        }*/
                        $csvretval = fputcsv($file, array_values($finalarray[0]));
                        //$flag++;

                        if ($csvretval != 0) {
                            array_push($csvarray, $csvretval);
                        } else {
                            //echo "PRODUCT NAME : ".$item['name']." FAILED TO IMPORT<br>";
                        }
                    }

                    //pradeep commented
                    if (strtolower($item['type']) != 'simple') {
                        $csvretvalother = fputcsv($fileother, array_values($finalarray[0]));

                        if ($csvretvalother != 0) {
                            array_push($csvotherarray, $csvretvalother);
                        } else {
                            //echo "PRODUCT NAME : ".$item['name']." FAILED TO IMPORT<br>";
                        }
                    }
                }
                //echo "csv generated";exit;

                $logFile = "syncProduct-" . date('Ymd') . ".log";

                $client->setLog("readCsvFile Started. ", null, $logFile);
                $logMsgs[] = "readCsvFile Started.";

                $client->setLog("Simple Product Import Started. ", null, $logFile);
                $logMsgs[] = "<strong>Simple Product Import Started.</strong>";
                
                $returnMsgs1 = $this->ProductsSync($productcsv, $syncPermissions, count($objCollection), $productIds, $ipAddress, $storeId);
                $logMsgs[] = implode('<br />', $returnMsgs1);

                $client->setLog("Other Product Import Started. ", null, $logFile);
                $logMsgs[] = "<strong>Other Product Import Started.</strong>";

                $returnMsgs2 = $this->ProductsSync($productcsvother, $syncPermissions, count($objCollection), $productIds, $ipAddress, $storeId);
                $logMsgs[] = implode('<br />', $returnMsgs2);

                $client->setLog("readCsvFile end. ", null, $logFile);
                $logMsgs[] = "readCsvFile end.";

                $client->setLog("The Products have been imported Successfully.", null, $logFile);
                $logMsgs[] = "The Products have been imported Successfully.";

                $message = 'success';
            } // main else
        } catch (\Exception $e) {
            $logMsgs[] = 'Error in processing';
            $logMsgs[] = ($e->getMessage());
            $message = $e->getMessage();
            $client->setLog("CSV Generation failed due to following reasons - " . print_r($e->getMessage(), true), null, $logFileName);
        }

        $client->setLog("reindexdata start.", null, $logFileName);
        $logMsgs[] = "reindexdata start.";
        $this->reindexdata();
        $client->setLog("reindexdata end.", null, $logFileName);
        $logMsgs[] = "reindexdata end.";

        /*-------WRITE LOG------*/
        $status = $success = 1;
        $result = \Neo\Winery\Model\Activity::LOG_SUCCESS;
        if (in_array('Error Occured', $logMsgs)) {
            $result = \Neo\Winery\Model\Activity::LOG_PARTIAL;
            $success = 0;
        }elseif (in_array('Error in processing', $logMsgs)) {
            $result = \Neo\Winery\Model\Activity::LOG_FAIL;
            $success = 0;
        }
        $logModel = $this->_log;
        $soapError = '';
        //echo "<pre>";echo implode('<br />', $logMsgs);exit;
        $logModel->setDescription(implode('<br />', $logMsgs))
             ->setEndTime(date('Y-m-d H:i:s'))
             ->setStatus($result)
             ->save();

        if ($productIds == '') {
            
            $clientnew = $client->connect();

            $clientnew->UpdateProductLastSynchDate(array('store_url'=>$store_url,
                                                                 'success'=>$success,
                                                                 'lastSynchDate'=>time(),
                                                                 'type'=>'product')); 
            $objectManager->get('\Qdos\QdosSync\Helper\Product\Position')->syncPosition($productIds = 0, $categoryId = 0, $storeId); 
        }else{
            $arrProductIds = explode('|', $productIds);
            $lastLogId = $logModel->getId();

            foreach ($arrProductIds as $key => $productId) {
                $product = $objectManager->get("\Magento\Catalog\Model\Product")
                           ->setStoreId($storeId)
                           ->load($productId);
                $product->setSyncStatus($result);              
                $product->setLastLogId($lastLogId);              
                $product->setLastSync(date('Y-m-d H:i:s'));  
                $product->save();            
            }
            $objectManager->get('\Qdos\QdosSync\Helper\Product\Position')->syncPosition($arrProductIds, $categoryId = 0, $storeId);
        }
        return $message;
    }// import product csv

    public function moveElement(&$array, $a, $b) {
        $p1 = array_splice($array, $a, 1);
        $p2 = array_splice($array, 0, $b);
        $array = array_merge($p2,$p1,$array);
    }

    /**
     * Auther: Ravi Mule
     * @param int $storeId
     * @param string $productIds
     * @return array|string
     * @throws \Exception
     */
    public function syncQty($storeId = 0, $productIds = '')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $base = $this->directory_list->getPath('lib_internal');
        $lib_file = $base . '/Connection.php';
        require_once($lib_file);
        $client = Test();

        $resultClient = $client->getConnect($storeId);

        $logFileName = "qdos-sync-stock-" . date('Ymd') . ".log";
        $logModel = $this->_log;
        $start_time = date('Y-m-d H:i:s');
        $logMsgs = $logMsg = $productLogIds = $hiddenProductArr = array();
        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipAddress = '';
        }
        $logModel->setActivityType('inventory')
            ->setStartTime($start_time)
            ->setStatus(\Neo\Winery\Model\Activity::LOG_PENDING)
            ->setIpAddress($ipAddress)
            ->setStoreId($storeId)
            ->save();
        $logMsgs[] = "Stock Update Initiated.";

        $client->setLog('Stock Update Initiated.', null, $logFileName, true);
        $product_id = 0; // 0 to get all products
        $product_type = ''; // 'All' to get all types of products

        $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');

        $ids_string = $productIds;
        /*if(count($productIds)){
            $ids_string = implode('|', $productIds);
        }*/
        $resultClient = $resultClient->GetProductQuantityArrayCSV(array('store_url' => $store_url, 'product_id' => 0, 'product_id_list' => $ids_string));

        $collection = array();
        $objCollection = array();
        if ($resultClient->outErrorMsg && strlen($resultClient->outErrorMsg) > 0) {
            $client->throwError($resultClient->outErrorMsg);
            //throw new \Magento\Exception('SOAP LOGIN ERROR' . $resultClient->outErrorMsg));
        } else {
            $result = $resultClient->GetProductQuantityArrayCSVResult;
            if (is_object($result) && isset($result->ProductQuantityCSV)) {
                $objCollection = $result->ProductQuantityCSV;
            }
        }

        // echo count($objCollection); exit;

        $client->setLog("count => " . count($objCollection), null, $logFileName);

        $logMsgs[] = "Total Count = " . count($objCollection);
        $client->setLog("CSV Generation starts", null, $logFileName);

        //echo "<pre>";print_r($objCollection);exit;

        if (count($objCollection)) {
            $filename = 'import_qty.csv';
            $logMsgs[] = "CSV creation started File Name : " . $filename;
            $CsvFile = $this->CreateCSV($objCollection, $filename, $client);
            $csv = new \Magento\Framework\File\Csv(new \Magento\Framework\Filesystem\Driver\File());
            $logMsgs[] = "CSV created.";
            $client->setLog("CSV generated.", null, $logFileName);
            $csvdata = $csv->getData($CsvFile);

            //echo "<pre>";print_r($csvdata);exit;
            $header = $csvdata[0];
            array_shift($csvdata);
            $attributes = array();
            foreach ($header as $attribute) {
                if ($attribute != 'product_id') {
                    $attributes[] = $attribute;
                }
            }
            //echo "<pre>dd";print_r($attributes);exit;
            $i = 0;
            $sync_sku_based = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/sku_based_product_sync');
            $productIds = array();
            foreach ($csvdata as $row) {
                $id = null;
                foreach ($header as $index => $key) {
                    if ($key == 'product_id') {
                        $productIds[$i] = $row[$index];
                        $id = $row[$index];
                    }
                    if ($sync_sku_based != 0) {
                        if ($key == 'sku') {
                            $productSkus[$i] = $row[$index];
                            $sku = $row[$index];
                        }
                    }
                }

                foreach ($header as $index => $key) {
                    if ($key != 'product_id') {

                        if ($sync_sku_based != 0) {
                            $records[$sku][$key] = $row[$index];
                        } else {
                            $records[$id][$key] = $row[$index];
                        }
                    }

                }
                $i++;
            }

            $logMsgs[] = "Total Records Fetched = " . count($records);
            $client->setLog("Total Records Fetched = " . count($records), null, $logFileName);
            //echo "<pre>ewrewr";print_r($records);exit;
            if ($sync_sku_based != 0) {

                $collection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
                    ->addAttributeToFilter("sku", array("in" => $productSkus));
                //->addStoreFilter($storeId);

            } elseif ($sync_sku_based == 0) {

                $collection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
                    ->addAttributeToFilter("entity_id", array("in" => $productIds));
                //->addStoreFilter($storeId);
            }

            $message = 'success';
            $logMsgs[] = "Import Started.";
            $client->setLog("Import Started.", null, $logFileName);
            $errors = array();
            if ($collectionCount = $collection->getSize()) {
                $logMsgs[] = "Total Product Found in Db : " . $collectionCount;
                $client->setLog("Total Product Found in Db : " . $collectionCount, null, $logFileName);
                $logModel->setDescription(implode('<br />', $logMsgs))
                    ->setStatus(\Neo\Winery\Model\Activity::LOG_PENDING)
                    ->save();
                $i = 0;
                //echo "<pre>";print_r($collectionCount);exit;
                try {
                    foreach ($collection as $product) {
                        /**
                         * for saving records storewise
                         */
                        $product = $objectManager->get("\Magento\Catalog\Model\Product")->load($product->getId());
                        $product->setStoreId($storeId);
                        if ($sync_sku_based != 0) {
                            $data = $records[$product->getSku()];
                            $stockdata = $objectManager->create("\Magento\CatalogInventory\Model\StockRegistry")->getStockItemBySku($product->getSku());
                        } elseif ($sync_sku_based == 0) {
                            $data = $records[$product->getId()];
                            //$stockdata = $objectManager->create("\Magento\CatalogInventory\Model\Stock\StockItemRepository")->get($product->getId());
                            $stockdata = $objectManager->create("\Magento\CatalogInventory\Api\StockRegistryInterface")->getStockItem($product->getId());
                        }

                        if ($stockdata->getProductId() == $product->getId()) {
                            //$stockdata->setData("qty", $data[$product->getId()]);
                            $stockdata->addData($data);
                            try {
                                $stockdata->save();
                                if (isset($data['status'])) {
                                    if ($data['status'] == 'Enabled') {
                                        $status = 1;
                                    } else if ($data['status'] == 'Disabled') {
                                        $status = 2;
                                    }
                                    $product->setData('status', $status);
                                }
                                $product->getResource()->saveAttribute($product, 'status');
                                $i++;
                                //$client->setLog("Total Product Updated : (".(int)$collectionCount - $i."). product Id : ".$product->getId(),null,$logFileName);
                            } catch (Exception $e) {
                                $logMsgs[] = "Error while updating product, Product Id : " . $product->getId() . ". Error ->" . $e->getMessage();
                                $client->setLog("Error while updating product, Product Id : " . $product->getId() . ". Error ->" . $e->getMessage(), null, $logFileName);
                                $message = $errors;
                            }
                        }
                    }
                    $status = 6;
                    if ($collectionCount == $i) {
                        $status = \Neo\Winery\Model\Activity::LOG_SUCCESS;
                        if (!$productIds) {
                            $success = 1;
                            $resultClient->UpdateProductLastSynchDate(array('store_url' => $store_url,
                                'success' => $success,
                                'lastSynchDate' => time(),
                                'type' => 'inventory'));
                            $client->setLog("updateProductLastSyncDate for stock updated.", null, $logFileName);
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                    $client->setLog("Exception While updating." . "<br />" . $e->getMessage(), null, $logFileName);
                    $message = $errors;
                }
            } else {
                $status = \Neo\Winery\Model\Activity::LOG_SUCCESS;
                $message = $logMsgs[] = "No Records Found In Database.";
                $client->setLog($message, null, $logFileName);
            }
            $logMsgs[] = "Import Finished. Total record updated : " . $i;
            $client->setLog("Import Finished. Total record updated : " . $i, null, $logFileName);

            try {
                $client->setLog("Indexing Started", null, $logFileName);
                $this->reindexdata();
                $client->setLog("Indexing Finished", null, $logFileName);
            } catch (Mage_Core_Exception $e) {
                $logMsgs[] = $e->getMessage();
                $client->setLog("Exception While Indexing" . "<br />" . $e->getMessage(), null, $logFileName);
            }
        } else {
            $status = \Neo\Winery\Model\Activity::LOG_SUCCESS;
            $message = $logMsgs[] = "No Records Found.";
            $client->setLog($message, null, $logFileName);
        }
        $client->setLog("Stock Update Finished", null, $logFileName);
        $logModel->setDescription(implode('<br />', $logMsgs))
            ->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus($status)
            ->save();
        return $message;
    }


    /**
     * bulk price update
     * gets data from service and creates CSV before importing data
     */
    public function syncPrice($storeId = 0, $productIds = '')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $base = $this->directory_list->getPath('lib_internal');
        $lib_file = $base . '/Connection.php';
        require_once($lib_file);
        $client = Test();

        $resultClient = $client->getConnect($storeId);

        $logFileName = "qdos-sync-price-" . date('Ymd') . ".log";
        $logModel = $this->_log;
        $start_time = date('Y-m-d H:i:s');
        $logMsgs = $logMsg = $productLogIds = $hiddenProductArr = array();
        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipAddress = '';
        }
        $logModel->setActivityType('price')
            ->setStartTime($start_time)
            ->setStatus(\Neo\Winery\Model\Activity::LOG_PENDING)
            ->setIpAddress($ipAddress)
            ->setStoreId($storeId)
            ->save();

        $logMsgs[] = "Price Update Initiated for store Id : " . $storeId;
        $client->setLog("Price Update Initiated for store Id : " . $storeId, null, $logFileName, true);

        $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');

        $ids_string = $productIds;
        /*if(count($productIds)){
            $ids_string = implode('|', $productIds);
        }*/

        $resultClient = $resultClient->GetProductPriceArrayCSV(array('store_url' => $store_url, 'product_id' => 0, 'product_id_list' => $ids_string, 'current_price_list_id' => 0));

        //echo "<pre>";print_r($resultClient);exit;

        $objCollection = array();
        if ($resultClient->outErrorMsg && strlen($resultClient->outErrorMsg) > 0) {
            $client->throwError($resultClient->outErrorMsg);
            //throw new \Magento\Exception('SOAP LOGIN ERROR' . $resultClient->outErrorMsg));
        } else {
            $result = $resultClient->GetProductPriceArrayCSVResult;
            if (is_object($result) && isset($result->ProductPriceCSV)) {
                $objCollection = $result->ProductPriceCSV;
            }
        }

        if (count($objCollection)) {
            $filename = 'import_price.csv';
            $logMsgs[] = "CSV creation started File Name : " . $filename;
            $CsvFile = $this->CreateCSV($objCollection, $filename, $client);
            $csv = new \Magento\Framework\File\Csv(new \Magento\Framework\Filesystem\Driver\File());
            $logMsgs[] = "CSV created.";
            $client->setLog("CSV generated.", null, $logFileName);
            $csvdata = $csv->getData($CsvFile);

            $header = $csvdata[0];
            array_shift($csvdata);
            $attributes = array();
            foreach ($header as $attribute) {
                if ($attribute != 'product_id') {
                    $attributes[] = $attribute;
                }
            }

            $i = 0;
            $productIds = array();
            foreach ($csvdata as $row) {
                $id = null;
                foreach ($header as $index => $key) {
                    if ($key == 'product_id') {
                        $productIds[$i] = $row[$index];
                        $id = $row[$index];
                    }
                }

                foreach ($header as $index => $key) {
                    if ($key != 'product_id') {
                        $records[$id][$key] = $row[$index];
                    }
                }

                $i++;
            }
            $logMsgs[] = "Total Records Fetched = " . count($records);
            $client->setLog("Total Records Fetched = " . count($records), null, $logFileName);

            $collection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
                ->addStoreFilter($storeId)
                ->addAttributeToSelect($attributes)
                ->addAttributeToFilter("entity_id", array("in" => $productIds));

            $message = 'success';
            $logMsgs[] = "Import Started.";
            $client->setLog("Import Started.", null, $logFileName);
            $errors = array();
            if ($collectionCount = $collection->getSize()) {
                $logMsgs[] = "Total Product Found in Db : " . $collectionCount;
                $client->setLog("Total Product Found in Db : " . $collectionCount, null, $logFileName);
                $logModel->setDescription(implode('<br />', $logMsgs))
                    ->setStatus(\Neo\Winery\Model\Activity::LOG_PENDING)
                    ->save();

                try {
                    $i = 0;
                    foreach ($collection as $product) {
                        //for saving records storewise
                        $product->setStoreId($storeId);
                        $data = $records[$product->getId()];
                        /**
                         * Calculating percent Special price for bundled item
                         */
                        if ($product->getTypeId() == 'bundle') {
                            if (isset($data['special_price'])) {
                                if (($data['price'] > 0) && ($data['special_price'] > 0)) {
                                    $data['special_price'] = ($data['special_price'] / $data['price']) * 100;
                                } elseif (($data['special_price'] > 0) && ($product->getPrice() > 0)) {
                                    $data['special_price'] = ($data['special_price'] / $product->getPrice()) * 100;
                                }
                            }
                        }

                        $product->addData($data);

                        $tax_class = null;
                        switch ($data['tax_class_id']) {
                            case 'None':
                                $tax_class = 0;
                                break;
                            case 'Taxable Goods':
                                $tax_class = 2;
                                break;
                            case 'Shipping':
                                $tax_class = 4;
                                break;
                        }
                        try {

                            $productId = $product->getId();
                            $productMassAction = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Catalog\Model\Product\Action');
                            $productMassAction->updateAttributes(
                                array($productId),
                                array('price' => $data['price'], 'special_price' => $data['special_price'], 'tax_class_id' => $tax_class, 'special_from_date' => $data['special_from_date'], 'special_to_date' => $data['special_to_date']),
                                $storeId
                            );

                            /**
                             * tier pricing
                             * sample data
                             * 2|101|12##3|102|10 i.e $website_id|cust_group|price_qty|price##$website_id|cust_group|price_qty|price
                             * above line shows example for updating the tier price of two stores
                             */
                            if ($objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/permissions/tier_price_sync') && isset($data['tier_prices'])) {

                                $product->setStoreId($storeId);
                                $tier_string = explode("|", $data['tier_prices']);

                                $store = $objectManager->get("\Magento\Store\Model\StoreManagerInterface")->getStore($storeId);
                                $website_id = $store->getWebsiteId();
                                $Ttmp = null;
                                $tier_price = array();
                                foreach ($tier_string as $TpriceDataString) {
                                    $TpriceData = explode("=", $TpriceDataString);

                                    $tier_price[] = array(
                                        'website_id' => $website_id,
                                        'cust_group' => $TpriceData[0],
                                        'price_qty' => $TpriceData[1],
                                        'price' => $TpriceData[2]
                                    );
                                }
                                $product->setTierPrice(array());
                                $product->save();
                                $product->setTierPrice($tier_price);
                                $product->save();
                            }
                            /* $qty = 7.00;//must be float value.
                                $price = 20.00;//must be float value.
                                $customerGroupId = 1;
                                $sku = '24-MB02';
                                try {
                                    $tierPriceData = $this->productTierPriceFactory->create();
                                    $tierPriceData->setCustomerGroupId($customerGroupId)
                                        ->setQty($qty)
                                        ->setValue($price);
                                    $tierPrice = $this->tierPrice->add($sku, $tierPriceData);
                                } catch (NoSuchEntityException $exception) {
                                    throw new NoSuchEntityException(__($exception->getMessage()));
                                }*/



                            $i++;
                        } catch (Exception $e) {
                            $client->setLog("Error while updating product, Product Id : " . $product->getId() . ". Error ->" . $e->getMessage(), null, $logFileName);
                            $logMsgs[] = "Error while updating product, Product Id : " . $product->getId() . ". Error ->" . $e->getMessage();
                            $errors = $logMsgs;
                        }
                    }
                    $status = 6;
                    if ($collectionCount == $i) {
                        $status = \Neo\Winery\Model\Activity::LOG_SUCCESS;
                        //Updating last sync date
                        if (!$productIds) {
                            $success = 1;
                            $resultClient->UpdateProductLastSynchDate(array('store_url' => $store_url,
                                'success' => $success,
                                'lastSynchDate' => time(),
                                'type' => 'price'));
                            $client->setLog("updateProductLastSyncDate for stock updated.", null, $logFileName);
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                    $message = $errors;
                    $client->setLog("Exception While updating." . "<br />" . $e->getMessage(), null, $logFileName);
                }
            } else {
                $status = \Neo\Winery\Model\Activity::LOG_SUCCESS;
                $message = $logMsgs[] = "No Records Found In Database.";
                $client->setLog($message, null, $logFileName);
            }
            $logMsgs[] = "Import Finished. Total record updated : " . $i;
            $client->setLog("Import Finished. Total record updated : " . $i, null, $logFileName);
            try {
                $client->setLog("Indexing Started", null, $logFileName);
                $this->reindexdata();
                $client->setLog("Indexing Finished", null, $logFileName);
            } catch (Mage_Core_Exception $e) {
                $logMsgs[] = $e->getMessage();
                $client->setLog("Exception While Indexing" . "<br />" . $e->getMessage(), null, $logFileName);
            }

        } else {
            $status = \Neo\Winery\Model\Activity::LOG_SUCCESS;
            $message = $logMsgs[] = "No Records Found.";
            $client->setLog($message, null, $logFileName);
        }

        $client->setLog("Price Update Finished", null, $logFileName);
        $logModel->setDescription(implode('<br />', $logMsgs))
            ->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus($status)
            ->save();

        return $message;
    }


    /**
     * creates CSV
     * @param $collection
     * @param $filename
     * @return string
     */
    protected function CreateCSV($collection, $filename, $client)
    {
        try {
            if (!file_exists($this->directory_list->getPath('var') . '/import/')) {
                mkdir($this->directory_list->getPath('var') . '/import/', 0777, true);
            }
            $priceCsv = $this->directory_list->getPath('var') . '/import/' . $filename;
            $headerflag = 0;
            $file = fopen($priceCsv, "w");
            $csvarray = array();
            foreach ($collection as $item) {
                $item = $this->convertItemToArray($item);
                $finalarray = array();
                array_push($finalarray, $item);
                if ($headerflag == 0) {
                    fputcsv($file, array_keys($finalarray[0]));
                    $headerflag = 1;
                }

                $csvretval = fputcsv($file, array_values($finalarray[0]));

                if ($csvretval != 0) {
                    array_push($csvarray, $csvretval);
                }
            }
            fclose($file);

            return $priceCsv;
        } catch (Exception $e) {
            $client->setLog("-----Error While Writing CSV-----", null, "Price_update.log", true);
            $client->setLog($e->getMessage(), null, "Price_update.log", true);
        }
    }

    public function exportNewsletter($subscriberData = null, $store_id = 0)
    {


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logModel = $this->_log;
        $logMsg = array();
        $message = 'success';
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/newsletter-sync.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("exportNewsletter start");

        $start_time = date('Y-m-d H:i:s');
        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipAddress = '';
        }
        $logModel->setActivityType('newsletter')
            ->setStartTime($start_time)
            ->setStatus(\Neo\Winery\Model\Activity::LOG_PENDING)
            ->setIpAddress($ipAddress)
            ->setStoreId($store_id)
            ->save();

        

        try {
            if (!empty($subscriberData) || $subscriberData != null) {

                $subscribeStatusFlag = true;
                $logger->info('subscriberId-Data' . $subscriberData['subscriber_id'], null, 'newsletter-sync.log', true);
                $subscribers = $objectManager->create('Magento\Newsletter\Model\ResourceModel\Subscriber\Collection')
                        ->addFieldToFilter('subscriber_id', $subscriberData['subscriber_id']);
                       

        
            } else {
                $subscribeStatusFlag = false;
                //$logger->info('subscriberElse', null, 'newsletter-sync.log', true);
                $subscribers = $objectManager->create('Magento\Newsletter\Model\ResourceModel\Subscriber\Collection');
            }
            $countSync = 0;
            foreach ($subscribers->getData() as $subscriber) {

               
                // check for sync subscribers record.
                if ($subscriber->getSyncflag() == Null || $subscribeStatusFlag) {
                    $customerExist = $objectManager->create("\Magento\Customer\Model\Customer")
                        ->getCollection()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('email', $subscriber->getEmail())
                        ->getFirstItem();

                    if (!empty($customerExist->getData())) {
                        $customerRegister = $this->exportCustomer($customerExist);
                        if ($customerRegister == false) {
                            $status = \Neo\Winery\Model\Activity::LOG_SUCCESS;
                        } else {
                            //$status = $logModel::LOG_ERROR;
                            $message = $customerRegister;
                        }


                    } else {
                        $logger->info('subscriber:' . $subscriber->getCustomerId(), null, 'newsletter-sync.log', true);
                        if ($subscriber->getCustomerId() == 0) {
                            $guestCustomer = $this->exportCustomerNonRegisteredFromSubscriber($subscriber);
                            if ($guestCustomer == false) {
                                $status = \Neo\Winery\Model\Activity::LOG_SUCCESS;
                            } else {
                                //$status = $logModel::LOG_ERROR;
                                $message = $guestCustomer;
                            }
                        }
                    }
                    $subscriber->setSyncflag(1);
                    $subscriber->save();
                    ++$countSync;
                }
              
            }
              $logger->info('Total Subscribers Sync: ' . $countSync, null, 'newsletter-sync.log', true);

        } catch (Exception $ex) {
            //$status = $logModel::LOG_ERROR;
            $logger->info('exportNewsletter failed.' . $ex->getMessage(), null, 'newsletter-sync.log', true);
            $message = $logMsg[] = 'exportNewsletter failed.' . $ex->getMessage();
        }

        $logModel->setDescription(implode('<br />', $logMsg))
            ->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus($status)
            ->save();
        return $message;

    }

//Set Customer Data
    public function setDataCustomer($customer, $orderId, $increment_id)
    {
         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();


       // $data = Mage::helper('qdossync/customer')->getAllCustomerAttribute($customer);

       // $customerMySize = Mage::getSingleton('core/resource')
         //   ->getConnection('core_write')
         //   ->isTableExists(Mage::getSingleton('core/resource')->getTableName('customer_mysize_value'));

     // /  if ($customerMySize){
     //        Mage::log('export my size',null,'qdos-sync.log',true);
     //        $sizeArr = Mage::helper('qdossync/customer')->exportMySize($customer);
     //        $data    = array_merge($data,$sizeArr);
     //    }
         $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($customer->getId());

        if($addresses = $customer->getAddresses()){
            $array = array();
            foreach($addresses as $address){
                $array[] = $address->getData();
            }
            $customer->setData('addresses',$array);
        }

        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber = $subscriber->loadByEmail($customer->getEmail());
        if($subscriber->getId()){
            $is_subscribed = 1; 
        }else{
           
            $is_subscribed = 0;
        }

        $data['WEBSITE']                     = $customer->getWebsiteId();
        $data['EMAIL']                       = $customer->getEmail();
        $data['GROUP_ID']                    = $customer->getGroupId();
        //$data['DISABLE_AUTO_GROUP_CHANGE'] = $customer->getDisableAutoGroupChange();
        $data['DISABLE_AUTO_GROUP_CHANGE']   = 0;
        $data['FIRSTNAME']                   = $customer->getFirstname();
        $data['LASTNAME']                    = $customer->getLastname();
        $data['PASSWORD_HASH']               = $customer->getPasswordHash();
        $data['CREATED_IN']                  = $customer->getCreatedIn();
        //$data['IS_SUBSCRIBED']               = $customer->getIsSubscribed()?$customer->getIsSubscribed():0;
        $data['IS_SUBSCRIBED']               = $is_subscribed ? $is_subscribed : 0;
        $data['GROUP']                       = '';
        $data['CUSTOMER_GROUP_ID']           = (int)$customer->getGroupId();
        $data['CUSTOMER_ID']                 = (int)$customer->getId(); //
        $data['ORDER_ID']                    = $orderId;
        $data['STYLIST_ID']                  = strlen($customer->getData('stylistid')) > 0?$customer->getStylistid():0;
        $data['INCREMENT_ID']                = $increment_id;

        // $stylePref = $customer->getData('style_answer');
        // if (strlen($stylePref) > 0){
        //     Mage::helper('qdossync/customer')->exportStylePreference($stylePref,$customer,$incrementId,$logMsg);
        // }
        // $iconClosest = $customer->getData('style_icon_closest');
        // if (strlen($iconClosest) > 0){
        //     Mage::helper('qdossync/customer')->exportStylistClosest($customer,$incrementId,$logMsg);
        // }
        
        $addressId = (int) $customer->getDefaultBilling();
        $billing   =  $customer->getAddressById($addressId);
        if($billing->getId()){
            $data['BILL_ADDR_FLAG']      = 1;
            $data['BILLING_PREFIX']      = is_null($billing->getPrefix())?'':$billing->getPrefix();
            $data['BILLING_SUFFIX']      = is_null($billing->getSuffix())?'':$billing->getSuffix();
            $data['BILLING_FIRSTNAME']   = $billing->getFirstname();
            $data['BILLING_MIDDLENAME']  = is_null($billing->getMiddlename())?'':$billing->getMiddlename();
            $data['BILLING_LASTNAME']    = $billing->getLastname();
            $data['BILLING_STREET_FULL'] = implode(' ',$billing->getStreet());
            $data['BILLING_STREET1']     = implode(' ',$billing->getStreet(1));
            $data['BILLING_STREET2']     = implode(' ',$billing->getStreet(2));
            $data['BILLING_STREET3']     = implode(' ',$billing->getStreet(3));
            $data['BILLING_STREET4']     = implode(' ',$billing->getStreet(4));
            $data['BILLING_STREET5']     = implode(' ',$billing->getStreet(5));
            $data['BILLING_STREET6']     = implode(' ',$billing->getStreet(6));
            $data['BILLING_STREET7']     = implode(' ',$billing->getStreet(7));
            $data['BILLING_STREET8']     = implode(' ',$billing->getStreet(8));
            $data['BILLING_CITY']        = $billing->getCity();
            $data['BILLING_REGION']      = $billing->getRegion();
            $data['BILLING_COUNTRY']     = $billing->getCountryId();
            $data['BILLING_POSTCODE']    = $billing->getPostcode();
            $data['BILLING_TELEPHONE']   = $billing->getTelephone();
            $data['BILLING_COMPANY']     = is_null($billing->getCompany())?'':$billing->getCompany();
            $data['BILLING_FAX']         = is_null($billing->getFax())?'':$billing->getFax();
        }else{
            $data['BILL_ADDR_FLAG']      = 0;
            $data['BILLING_PREFIX']      = '';
            $data['BILLING_SUFFIX']      = '';
            $data['BILLING_FIRSTNAME']   = '';
            $data['BILLING_MIDDLENAME']  = '';
            $data['BILLING_LASTNAME']    = '';
            $data['BILLING_STREET_FULL'] = '';
            $data['BILLING_STREET1']     = '';
            $data['BILLING_STREET2']     = '';
            $data['BILLING_STREET3']     = '';
            $data['BILLING_STREET4']     = '';
            $data['BILLING_STREET5']     = '';
            $data['BILLING_STREET6']     = '';
            $data['BILLING_STREET7']     = '';
            $data['BILLING_STREET8']     = '';
            $data['BILLING_CITY']        = '';
            $data['BILLING_REGION']      = '';
            $data['BILLING_COUNTRY']     = '';
            $data['BILLING_POSTCODE']    = '';
            $data['BILLING_TELEPHONE']   = '';
            $data['BILLING_COMPANY']     = '';
            $data['BILLING_FAX']         = '';
        }
        
        $addressId = (int) $customer->getDefaultShipping();
        $shipping = $customer->getAddressById($addressId);
        if($shipping->getId()){
            $data['SHIP_ADDR_FLAG']       = 1;
            $data['SHIPPING_PREFIX']      = is_null($shipping->getPrefix())?'':$shipping->getPrefix();
            $data['SHIPPING_SUFFIX']      = is_null($shipping->getSuffix())?'':$shipping->getSuffix();
            $data['SHIPPING_FIRSTNAME']   = $shipping->getFirstname();
            $data['SHIPPING_MIDDLENAME']  = is_null($shipping->getMiddlename())?'':$shipping->getMiddlename();
            $data['SHIPPING_LASTNAME']    = $shipping->getLastname();
            $data['SHIPPING_STREET_FULL'] = implode(' ',$shipping->getStreet());
            $data['SHIPPING_STREET1']     = implode(' ',$shipping->getStreet(1));
            $data['SHIPPING_STREET2']     = implode(' ',$shipping->getStreet(2));
            $data['SHIPPING_STREET3']     = implode(' ',$shipping->getStreet(3));
            $data['SHIPPING_STREET4']     = implode(' ',$shipping->getStreet(4));
            $data['SHIPPING_STREET5']     = implode(' ',$shipping->getStreet(5));
            $data['SHIPPING_STREET6']     = implode(' ',$shipping->getStreet(6));
            $data['SHIPPING_STREET7']     = implode(' ',$shipping->getStreet(7));
            $data['SHIPPING_STREET8']     = implode(' ',$shipping->getStreet(8));
            $data['SHIPPING_CITY']        = $shipping->getCity();
            $data['SHIPPING_REGION']      = $shipping->getRegion();
            $data['SHIPPING_COUNTRY']     = $shipping->getCountryId();
            $data['SHIPPING_POSTCODE']    = $shipping->getPostcode();
            $data['SHIPPING_TELEPHONE']   = $shipping->getTelephone();
            $data['SHIPPING_COMPANY']     = is_null($shipping->getCompany())?'':$shipping->getCompany();
            $data['SHIPPING_FAX']         = is_null($shipping->getFax())?'':$shipping->getFax();
        }else{
            $data['SHIP_ADDR_FLAG']       = 0;
            $data['SHIPPING_PREFIX']      = '';
            $data['SHIPPING_SUFFIX']      = '';
            $data['SHIPPING_FIRSTNAME']   = '';
            $data['SHIPPING_MIDDLENAME']  = '';
            $data['SHIPPING_LASTNAME']    = '';
            $data['SHIPPING_STREET_FULL'] = '';
            $data['SHIPPING_STREET1']     = '';
            $data['SHIPPING_STREET2']     = '';
            $data['SHIPPING_STREET3']     = '';
            $data['SHIPPING_STREET4']     = '';
            $data['SHIPPING_STREET5']     = '';
            $data['SHIPPING_STREET6']     = '';
            $data['SHIPPING_STREET7']     = '';
            $data['SHIPPING_STREET8']     = '';
            $data['SHIPPING_CITY']        = '';
            $data['SHIPPING_REGION']      = '';
            $data['SHIPPING_COUNTRY']     = '';
            $data['SHIPPING_POSTCODE']    = '';
            $data['SHIPPING_TELEPHONE']   = '';
            $data['SHIPPING_COMPANY']     = '';
            $data['SHIPPING_FAX']         = '';
        }
       
        return $data;
    }






    public function exportCustomer($customer, $orderId = -1, $incrementId = 0, &$logMsg = array())
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeId = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            $store = $objectManager->get("\Magento\Store\Model\StoreManagerInterface")->getStore($storeId);
            $websiteId = $store->getWebsiteId();
            $base = $this->directory_list->getPath('lib_internal');
            $lib_file = $base . '/Connection.php';
            require_once($lib_file);
            $client = Test();

            $resultClient = $client->getConnect($storeId);
            $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $client->setLog("StoreId:" . $storeId . '|store_url:' . $store_url, null, 'newsletter-sync.log', true);

           $data =$this->setDataCustomer($customer, $orderId, $incrementId);
            // $data['ADDITIONAL_PARAMETERS'] = 'shoesize:'.$customer->getShoesize().'|birthday:'.$customer->getBirthday().'|mobilephone:'.$customer->getMobilephone().'|intrested:'.$customer->getIntrested().'|hearaboutus:'.$customer->getHearaboutus().'|firstname:'.$customer->getFirstname().'|lastname:'.$customer->getLastname();
          
          $client->setLog("CUSTOMER SENDING", null, 'newsletter-sync.log', true);
            $client->setLog($data, null, 'newsletter-sync.log', true);
            
            $result = $resultClient->CreateCustomer(array('store_url' => $store_url, 'orderID' => $orderId, 'customer' => $data));

            $result =(array)$result;// $this->convertObjToArray($result);

       
            if ($result['outErrorMsg'] == '') {
                $error = false;
                $client->setLog('Send Customer Success: ' . $customer->getId(), null, 'newsletter-sync.log', true);
                $logMsg[] = 'Send Customer Success: ' . $customer->getId();
            } else {
                //$error = true;
                $client->setLog('Customer: ' . $result['outerrormsg'], null, 'newsletter-sync.log', true);
                $error = $logMsg[] = 'Error send Customer: ' . $result['outerrormsg'];
            }
            return implode(",", $logMsg);
        } catch (Exception $e) {
            $client->setLog('Send Customer in Order Failed: ' . $e->getMessage(), null, 'newsletter-sync.log', true);
            $error = $logMsg[] = 'Send Customer in Order Failed: ' . $e->getMessage();
            return false;
        }
        return $error;
    }

    public function exportCustomerNonRegisteredFromSubscriber($subscriber)
    {
        try {
            $logMsg=array();
            //$data = Mage::helper('sync/customer')->getAllCustomerAttribute();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $data = $objectManager->get('Magento\Customer\Model\Customer')->getAttributes();
            $storeId = $subscriber->getStoreId();

            $base = $this->directory_list->getPath('lib_internal');
            $lib_file = $base . '/Connection.php';
            require_once($lib_file);
            $client = Test();

            $resultClient = $client->getConnect($storeId);

            $store = $objectManager->get("\Magento\Store\Model\StoreManagerInterface")->getStore($storeId);
            $websiteId = $store->getWebsiteId();
            $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $store_name = $objectManager->get("\Magento\Store\Model\StoreManagerInterface")->getStore()->getName();
            $orderId = -1;

            $client->setLog('QDOS subscriber data', null, 'newsletter-sync.log');
           

            $data['WEBSITE'] = $websiteId;
            $data['EMAIL'] = $subscriber->getSubscriberEmail();
            $data['GROUP_ID'] = '0';
            $data['DISABLE_AUTO_GROUP_CHANGE'] = 0;
            $data['FIRSTNAME'] = $subscriber->getFirstname() ? $subscriber->getFirstname() : '';
            $data['LASTNAME'] = $subscriber->getLastname() ? $subscriber->getLastname() : '';
            $data['PASSWORD_HASH'] = md5('qdos');
            $data['CREATED_IN'] = $store_name;
            $data['IS_SUBSCRIBED'] = $subscriber->getSubscriberStatus() ? '1' : 0;
            $data['GROUP'] = '';
            $data['CUSTOMER_GROUP_ID'] = 0;
            $data['CUSTOMER_ID'] = 1;
            $data['ORDER_ID'] = $orderId;
            $data['STYLIST_ID'] = 0;
            $data['ADDITIONAL_PARAMETERS'] = '';
            $data['ADDITIONAL_PARAMETERS'] = 'shoesize:' . $subscriber->getShoesize() . '|birthday:' . $subscriber->getBirthday() . '|mobilephone:' . $subscriber->getMobilephone() . '|interested:' . $subscriber->getIntrested() . '|hearaboutus:' . $subscriber->getHearaboutus() . '|firstname:' . $subscriber->getFirstname() . '|lastname:' . $subscriber->getLastname();

            //billing Address
            $prefix = array('BILLING', 'SHIPPING');
            $data['BILL_ADDR_FLAG'] = 0;
            $data['SHIP_ADDR_FLAG'] = 0;
            //Added by Shailendra Gupta on 15 sept 2014 for handling new parameters in the webservice

            //End by Shailendra Gupta            
            foreach ($prefix as $pre) {
                $data[$pre . '_PREFIX'] = '';
                $data[$pre . '_SUFFIX'] = '';
                $data[$pre . '_FIRSTNAME'] = '';
                $data[$pre . '_MIDDLENAME'] = '';
                $data[$pre . '_LASTNAME'] = '';
                $data[$pre . '_STREET_FULL'] = '';
                $data[$pre . '_STREET1'] = '';
                $data[$pre . '_STREET2'] = '';
                $data[$pre . '_STREET3'] = '';
                $data[$pre . '_STREET4'] = '';
                $data[$pre . '_STREET5'] = '';
                $data[$pre . '_STREET6'] = '';
                $data[$pre . '_STREET7'] = '';
                $data[$pre . '_STREET8'] = '';
                $data[$pre . '_CITY'] = '';
                $data[$pre . '_REGION'] = '';
                $data[$pre . '_COUNTRY'] = '';
                $data[$pre . '_POSTCODE'] = '';
                if ($pre == 'BILLING') {
                    $data[$pre . '_TELEPHONE'] = $subscriber->getMobilephone() ? $subscriber->getMobilephone() : '';
                } else {
                    $data[$pre . '_TELEPHONE'] = '';
                }
                $data[$pre . '_COMPANY'] = '';
                $data[$pre . '_FAX'] = '';

            }
            // $client->setLog($subscriber->getData(), null, 'newsletter-sync.log');
          
            $result = $resultClient->CreateCustomerCSV(array('store_url' => $store_url, 'orderID' => $orderId, 'customer' => $data));
            if ($result->outErrorMsg && strlen($result->outErrorMsg) > 0) {
                $client->setLog('Customer: ' . $result->outErrorMsg, null, 'newsletter-sync.log', true);
                $error = 'Customer: ' . $result->outErrorMsg;
            } else {
                try {
                    if ($result->CreateCustomerCSVResult) {
                        $logMsg[]='Send Customer form Subscriber success';
                        $client->setLog('Send Customer form Subscriber success', null, 'newsletter-sync.log', true);
                        $error = false;
                    }
                } catch (Exception $e) {
                    $error = 'Send Customer in Subscriber Error: ' . $e->getMessage();
                    $client->setLog('Send Customer in Subscriber Error: ' . $e->getMessage(), null, 'newsletter-sync.log', true);
                    return $error;
                }
            }

        } catch (Exception $e) {
            $error = 'Send Customer in Subscriber Failed: ' . $e->getMessage();
            $client->setLog('Send Customer in Subscriber Failed: ' . $e->getMessage(), null, 'newsletter-sync.log', true);
            return $error;
        }
       
        return implode(" ", $logMsg);
    }

    public function getSyncPermission($storeId)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/storemapping.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $syncPermissions = array();
        $arrSyncPerm = $objectManager->create('\Qdos\QdosSync\Model\Storemapping')
            ->getCollection()
            ->addFieldToSelect('sync_type')
            ->addFieldToFilter('store_id', $storeId)
            ->load()
            ->getData();

        $logger->info('store id : ' . $storeId);
        $logger->info('storemapping array : ' . print_r($arrSyncPerm, true));

        if ($arrSyncPerm){
            $syncPermissions = explode(',', $arrSyncPerm[0]['sync_type']);
        }
        return $syncPermissions;
    }



}