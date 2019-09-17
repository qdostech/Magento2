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

// use \Psr\Log\LoggerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_pushArr = array();
    protected $_i = 0;
    protected $_lostDataArr = array();
    protected $eavSetup;
    protected $_result = true;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */

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
        \Qdos\QdosSync\Model\Syncattribute $syncattributelog
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
            array('value' => 'Manual Sync Product', 'label' => 'Manual Sync Product')
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

    public function syncAttribute($ipAddress = '')
    {
        // $this->_logger->info(__METHOD__);
        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base . '/Test.php';
        require_once($lib_file);
        $client = Test();
        $logFileName = "import-" . date('Ymd') . ".log";
        $client->setLog("Sync Attribute ", null, $logFileName);
        $allCategories = array();
        $resultClient = $client->connect();
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
    private function ProductsSync($filePath, $syncPermissions, $count = 1, $product_id = null, $ipAddress = '')
    {
        ini_set('default_socket_timeout', 900); // or whatever new value you want
        $logFileName = "syncProduct-" . date('Ymd') . ".log";

        // $this->_logger->info(__METHOD__);
        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base . '/Test.php';
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
            ->setActivityType('product')
            ->save();

        $logFileName = "syncProduct-" . date('Ymd') . ".log";

        $client->setLog("Simple Product Import Started. ", null, $logFileName);
        $logMsg[] = "<strong>Simple Product Import Started.</strong>";
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $success = 0;
        $fail = 0;
        try {
            $importHandler = $objectManager->create('CommerceExtensions\ProductImportExport\Model\Data\CsvImportHandler');
            $client->setLog("readCsvFile Started. ", null, $logFileName);
            $logMsg[] = "readCsvFile Started.";
            if ($count > 0) {
                $logMsg = $importHandler->readCsvFile($filePath, $syncPermissions, $client, $logMsg);
            }
            $client->setLog("readCsvFile end. ", null, $logFileName);
            $logMsg[] = "readCsvFile end.";
            $client->setLog("The Products have been imported Successfully.", null, $logFileName);
            $logMsg[] = "The Products have been imported Successfully.";
            $client->setLog("reindexdata start.", null, $logFileName);
            $logMsg[] = "reindexdata start.";
            $this->reindexdata();
            $client->setLog("reindexdata end.", null, $logFileName);
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

        $client->setLog("Result: " . $result . ' & ' . $success, null, $logFileName);
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
        return;
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

    public function syncCategory($ipAddress = '')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $base = $this->directory_list->getPath('lib_internal');
        $lib_file = $base . '/Test.php';
        require_once($lib_file);
        $client = Test();
        $logFileName = "syncCategory-" . date('Ymd') . ".log";
        $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');
        try {
            $client->setLog("Sync Category Started ", null, $logFileName);
            $allCategories = array();
            $resultClient = $client->connect();
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
                ->setActivityType('category')
                ->save();

            $this->_synccategorieslog->setStart($start_time)
                ->setFinish(date('Y-m-d H:i:s'))
                ->setStatus(\Qdos\QdosSync\Model\Activity::LOG_PENDING)
                ->setIpAddress($ipAddress)
                ->setActivity('category')
                ->save();

            $logMsg[] = "Sync categories  starts";
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
            // $this->_deleteAllCategories($storeId,$this->_pushArr,$client);
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
            $category->setUrlKey($setPath . $data['id']);
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
            // $allRoot = $this->getAllRootCategoryId();
            $rootId = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getRootCategoryId();
            foreach ($collection as $category) {
                if (!in_array($category->getId(), $importData) &&
                    // !in_array($category->getId(),$allRoot) &&
                    !in_array($category->getId(), array($rootId, \Magento\Catalog\Model\Category::TREE_ROOT_ID))
                ) {
                    $category->delete();
                    $client->setLog('_deleteAllCategories', null, $logFileName);
                    // $log = Mage::getModel('qdossync/category_log')->loadByCategoryId($category->getId());
                    // if (!empty($log) && $log->getId()){
                    //     $log->delete();
                    // }
                }
            }
        } catch (Exception $e) {
            $client->setLog($e->getMessage(), null, $logFileName);
        }
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
            $categoryTmp->setUrlKey($url . $catId);
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

        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base . '/Test.php';
        require_once($lib_file);
        $client = Test();
        try {
            $resultClient = $client->connect();
            $product_id = 0; // 0 to get all products
            $product_type = ''; // 'All' to get all types of products
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
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

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                /** @var    \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
                $arrProducts = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')->addAttributeToSelect('url_key');

                foreach ($arrProducts as $product) {
                    if (in_array($product->getUrlKey(), $arrUrlKey)) {
                        $urlKey = $product->getUrlKey();
                        if ($urlKey == '') {
                            $lowerName = rtrim(strtolower($product->getName()));
                            $urlKey = str_replace(' ', '-', $lowerName);
                            $client->setLog('1. If' . $product->getName(), null, $logFileName);
                        }
                        if (in_array($urlKey, $arrUrlKey)) {
                            $randomNo = mt_rand(100000, 999999);
                            $newUrlKey = $urlKey . '-' . $randomNo;
                            $client->setLog('2. If' . $product->getName(), null, $logFileName);
                            $UpdatedUrlKey = $newUrlKey;
                        } else {
                            $UpdatedUrlKey = $urlKey;
                            $client->setLog('3. Else' . $product->getName(), null, $logFileName);
                        }
                        $arrUrlKey[$product->getSku()] = $UpdatedUrlKey;
                    } else {
                        $client->setLog('4. Else' . $product->getName(), null, $logFileName);
                        $arrUrlKey[$product->getSku()] = $product->getUrlKey();
                    }
                }

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

                foreach ($collection as $item) {
                    $item = $this->convertItemToArray($item);
                    if (isset($item['url_key'])) {
                        if (!empty($arrUrlKey[$item['sku']])) {
                            if ($arrUrlKey[$item['sku']] != $item['url_key']) {
                                if (in_array($item['url_key'], $arrUrlKey)) {
                                    $urlKey = $item['url_key'];
                                    if ($urlKey == '') {
                                        $lowerName = rtrim(strtolower($item['name']));
                                        $urlKey = str_replace(' ', '-', $lowerName);
                                    }
                                    if (in_array($urlKey, $arrUrlKey)) {
                                        $randomNo = mt_rand(100000, 999999);
                                        $newUrlKey = $urlKey . '-' . $randomNo;
                                        unset($item['url_key']);
                                        $item['url_key'] = $newUrlKey;
                                    } else {
                                        unset($item['url_key']);
                                        $item['url_key'] = $urlKey;
                                    }
                                }
                                $client->setLog('If ' . $item['sku'] . ' - ' . $item['url_key'], null, $logFileName);
                            } else {
                                $urlKey = $item['url_key'];
                                $client->setLog('In Else : ' . $urlKey, null, $logFileName);
                                if ($urlKey == '') {
                                    $lowerName = rtrim(strtolower($item['name']));
                                    $urlKey = str_replace(' ', '-', $lowerName);
                                }
                                if (in_array($urlKey, $arrUrlKey)) {
                                    $randomNo = mt_rand(100000, 999999);
                                    $newUrlKey = $urlKey . '-' . $randomNo;
                                    unset($item['url_key']);
                                    $item['url_key'] = $newUrlKey;
                                } else {
                                    unset($item['url_key']);
                                    $item['url_key'] = $urlKey;
                                }
                                $client->setLog('Else ' . $item['sku'] . ' - ' . $item['url_key'], null, $logFileName);
                            }
                        }
                    } else {
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
                            $item['group_price_price'] = $strPercentGroupPrice;
                        }
                        unset($item['groupid_price']);
                    } else {
                        $item['bundle_options'] = '';
                        $item['bundle_selections'] = '';
                        if (isset($item['groupid_price'])) {
                            $groupPrice = str_replace(':', '=', $item['groupid_price']);
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
                                $tierPrice = str_replace(":", "=", $item['tier_price']);
                            }
                            //End by Shailendra Gupta for tier price sync
                            $item['tier_prices'] = $tierPrice;
                            //$item['tier_prices'] = '0=5=19|1=10=18';
                            //$item['tier_prices'] = '0=4=6.80|1=4=6.80|6=4=6.80';
                        } else {
                            $item['tier_prices'] = '';
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
                        $csvretval = fputcsv($file, array_values($finalarray[0]));

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

                /**
                 * Refreshes the cache berfore the imports starts
                 */
                //$cache_response_msg = Mage::helper('sync')->CacheRefresh();
                //$logMsgs = array_merge($logMsgs, $cache_response_msg);
                $returnMsgs = $this->ProductsSync($productcsv, $syncPermissions, count($objCollection), $productIds, $ipAddress);
                $returnMsgs = $this->ProductsSync($productcsvother, $syncPermissions, count($objCollection), $productIds, $ipAddress);
                $logMsgs[] = $returnMsgs;

                $message = 'success';
            } // main else
        } catch (Exception $e) {
            $logMsgs[] = 'Error in processing';
            $logMsgs[] = $this->decodeErrorMsg($e->getMessage());
            $message = $e->getMessage();
            $client->setLog("CSV Generation failed due to following reasons - " . print_r($e->getMessage(), true), null, $logFileName);
        }
        return $message;
    }// import product csv


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
        $lib_file = $base . '/Test.php';
        require_once($lib_file);
        $client = Test();

        $resultClient = $client->connect();

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
        $lib_file = $base . '/Test.php';
        require_once($lib_file);
        $client = Test();

        $resultClient = $client->connect();

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
                $status = $logModel::LOG_SUCCESS;
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
}