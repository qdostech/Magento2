<?php
/**
 * Copyright © 2015 Qdos . All rights reserved.
 */
namespace Qdos\Sync\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
  protected $_paramsService = array();
  protected $_storeIds;
   
	public function __construct(
    \Magento\Framework\App\Helper\Context $context,
    \Magento\Framework\App\Filesystem\DirectoryList $directory_list
	) {
        $this->directory_list = $directory_list;
		parent::__construct($context);
        
	}

    public function getSettingsKey($key){
        $defaultCountry = $this->scopeConfig->getValue('general/store_information/country_id');
        return Mage::getStoreConfig('qdosConfig/settings/'.$key);
    }

    public function getCronSettingsKey($cron_type = '',$key = 'enable'){
        return Mage::getStoreConfig('cron_settings/'.$cron_type.'/'.$key);
    }

    public function getBatchImportSettingsKey($key){
        return Mage::getStoreConfig('qdosConfig/batch_import/'.$key);
    }

    public function getExportSettingsKey($key){
        return Mage::getStoreConfig('qdosConfig/export_settings/'.$key);
    }

    public function getImportSettingsKey($key){
        return Mage::getStoreConfig('qdosConfig/import_settings/'.$key);
    }

    public function getPermissionsKey($key){
        return Mage::getStoreConfig('qdosConfig/permissions/'.$key);
    }

    public function getEmailErrorKey($key = 'enabled'){
        return Mage::getStoreConfig('qdosConfig/email_error/'.$key);
    }

    public function updateNewInfo(){
        return Mage::getStoreConfig('qdosConfig/export_settings/enable_update_new');
    }

    public function isDebugMode(){
        return Mage::getStoreConfig('qdosConfig/permissions/debug_mode');
    }

    public function enableVoucher(){
        return Mage::getStoreConfig('qdosConfig/settings/enable_voucher');
    }

    public function getDeleteSettingsKey($key){
        return Mage::getStoreConfig('qdosConfig/delete_settings/'.$key);
    }

    public function showArrayClear($array){
        print_r('<pre>');
        print_r($array);
        print_r('</pre>');
    }

    public function run($client,$methodName = '', $params = array()){
        $resultClient = $client->$methodName($params);
        return $resultClient;
    }
    
    public function getStatusColor($value){
        /*$color = array(0=>'#E41101',
                       1=>'#0000FF',
                       2=>'#33CC00',
                       3=>'#CCCCCC');*/
        $color = array(0=>'#E41101',
                       1=>'#3CB861',
                       2=>'#FF9C00',
                       3=>'#CCCCCC',
                       4=>'#F55600',
                       5=>'#0066FF',
                       6=>'#0000FF');
        if (isset($color[$value])){
            return $color[$value];
        }else{
            return 'white';
        }
    }
    
    public function getSoapParams($soapFunctions = array(),$functionName = '',$paramName = ''){
        $details = array();
        $params = $this->getParamsDetail($soapFunctions,$functionName);
        foreach ($params as $value){
            $d = explode(' ',trim($value));
            if (is_array($d) and count($d) > 0){
                if ($d[1] == $paramName){
                    $type = $d[0];
                    $details = $this->getParamsDetail($soapFunctions,$type);
                    if (count($details) == 0){
                        $details = (array)$value;
                    }else{
                         while (count($details) == 1){
                            $param = $details[0];
                            $d = explode(' ', trim($param));
                            $details = $this->getParamsDetail($soapFunctions, $d[0]);
                         }
                    }
                }
            }
        }
        return $details;
    }

    public function getParamsDetail($soapFunctions = array(), $structName = ''){
        $params_ar = array();
        foreach ($soapFunctions as $value){
            if (preg_match('/^struct\\s'.$structName.'\\s/',$value)){
                $string = explode('{',trim($value));
                $p = substr($string[1],0,-1);
                $params_ar = explode(';',$p);
                array_pop($params_ar);
            }
        }
        return $params_ar;
    }

    public function moduleIsExist($namespace = '',$module_name = ''){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $_moduleManager = $om->get('Magento\Framework\Module\Manager');
        return $_moduleManager->isEnabled($namespace.'_'.$module_name);
    }

    public function tableColumnExist($tableName,$columnName){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName($tableName);
        // $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        // $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);
        return $connection->tableColumnExists($tableName, $columnName, null);
    }

    public function getAllStore(){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
        $allStores = $storeManager->getStores($withDefault = false);

        $options = array();
        foreach ($allStores as $_eachStoreId => $val){
            $options[] = $storeManager->getStore($_eachStoreId)->getId();
        }
        return $options;
    }
    function convert($size){
        if ($size > 0){
            $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
            return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
        }else{
            return 0;
        }
    }

    public function convertItemToArray($object){
        $new = array();
        if(is_object($object)){
            $new = array_change_key_case((array)$object, CASE_LOWER);
        }
        if(is_array($object)) {
           return $object;
        }
        return $new;
    }

   
    
}