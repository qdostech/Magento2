<?php
/**
 * Copyright © 2015 Qdos . All rights reserved.
 */
namespace Qdos\CustomerSync\Helper;
use Magento\Framework\App\Bootstrap;
// use Magento\TestFramework\Helper\Bootstrap;

include('app/bootstrap.php');
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $customerReourceFactory;
    protected $customerModel;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Qdos\Sync\Helper\Api $helperData,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerReourceFactory
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->directory_list = $directory_list;
        $this->customerModel = $customerModel;
        $this->customerResourceFactory = $customerReourceFactory;
    }
    
    protected function convertObjToArray($object){
        $new = array();
        if(is_object($object)){
            $new[] = $object;
        }
        if(is_array($object)) {
           $new = $object;
        }
        return $new;
    }

    //GetCustomerGroup
    public function importCustomerGroup($store_id = 1){
        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base.'/Test.php';
        require_once($lib_file);
        $client = Test();
        $logFileName = "import-customergroups".date('Ymd').".log";
        $client->setLog("Sync Attribute ",null,$logFileName);
        $resultClient = $client->connect();
        $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');
        $resultClient = $resultClient->GetCustomerGroupCSV(array('store_url'=>$store_url));
        if ($resultClient->outErrorMsg && strlen($resultClient->outErrorMsg) > 0) {
            $client->setLog($resultClient->outErrorMsg, null, 'qdos-sync-attribute.log', true);
        }
        $error = false;
        $success = 0;
        $fail = 0;
        $start_time = date('Y-m-d H:i:s');
        /*get Update Products Only value end*/
        // $this->_log->setStartTime($start_time)
        //     ->setEndTime(date('Y-m-d H:i:s'))
        //     ->setStatus(\Qdos\Sync\Model\Sync::LOG_PENDING)
        //     ->setIpAddress($_SERVER['REMOTE_ADDR'])
        //     ->setActivityType('attribute')
        //     ->save();

        
        
        // $start_time = date('Y-m-d H:i:s', Mage::app()->getLocale()->storeTimeStamp());
        // $logModel = Mage::getModel('sync/sync');
        // if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
        //     $ipAddress = $_SERVER['REMOTE_ADDR'];
        // }else{
        //     $ipAddress = '';
        // } 
        // $logModel->setStartTime($start_time)
        //             ->setEndTime(1)
        //             ->setStatus($logModel::LOG_PENDING)
        //             ->setIpAddress($ipAddress)
        //             ->setActivityType('customer_group')
        //             ->save();


        $logMsg = array();
        $error = false;
        $logMsg[] = "Customer Group import Initiated";
        if ($resultClient->outErrorMsg && strlen($resultClient->outErrorMsg) > 0){
            $error = true;
            $logMsg[] = "Error while fetching data from Webservice.";
            $logMsg[] = (string)$resultClient->outErrorMsg;
            $status = $logModel::LOG_FAIL;
        }else{
            $result = $resultClient->GetCustomerGroupCSVResult;
            if (is_object($result) && $result->CustomerGroupCSV) {
                $customerGroupArr = $this->convertObjToArray($result->CustomerGroupCSV);
                $logMsg[] = "Total group Fetched : ".count($customerGroupArr);
                $i = 0;
                // echo "<pre>"; print_r($customerGroupArr); exit;
                foreach ($customerGroupArr as $_group) {
                    $insert = false;
                    if (isset($_group->CUSTOMER_GROUP_ID) && is_numeric($_group->CUSTOMER_GROUP_ID)) {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $group = $objectManager->create(\Magento\Customer\Model\Group::class)->load($_group->CUSTOMER_GROUP_ID);
                        if ($group->getId()){
                            // Mage::log($group->getId(),null,"qdos-sync-customer-".date('Ymd').".log",true);
                        }else{
                            $insert = true;
                        }
                        $group->setCode((string)$_group->CUSTOMER_GROUP_CODE)
                              ->setTaxClassId(3);
                        try {
                           $group->save();
                           if ($insert){
                                $new_id = $group->getId();
                                try{
                                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                    $connection = $resource->getConnection();
                                    $tableName = $resource->getTableName('customer_group');

                                    //Update Data into table
                                    $sql = "Update " . $tableName . " Set customer_group_id = ".(int)$_group->CUSTOMER_GROUP_ID." where customer_group_id = ".$new_id;
                                    $connection->query($sql);
                               }catch(Exception $e){
                                   // Mage::log($e->getMessage(), null, "qdos-sync-customer-".date('Ymd').".log", true);
                               }
                           }
                            $logMsg[] = "Group Imported with ID : ".$_group->CUSTOMER_GROUP_ID;
                            $i++;
                        } catch (Exception $e) {
                            $logMsg[] = $e->getMessage();
                            // Mage::log($e->getMessage(), null, "qdos-sync-customer-".date('Ymd').".log", true);
                            $error = true;
                        }
                    }
                }
                // $status = $logModel::LOG_PARTIAL;
                if($i == count($customerGroupArr)){
                    $logMsg[] = "Total group Imported : ".$i;
                    // $status = $logModel::LOG_SUCCESS;
                }
            }else{
                $logMsg[] = "No Records Fetched.";
                // $status = $logModel::LOG_SUCCESS;
            }
        }
        $logMsg[] = "Import Completed";
            /*-------WRITE LOG------*/
        // $logModel->setEndTime(date('Y-m-d H:i:s', Mage::app()->getLocale()->storeTimeStamp()))
        //          ->setStatus($status)
        //          ->setDescription(implode('<br />',$logMsg))
        //          ->save();
        return !$error;
    }

    public function syncCustomersold(){
        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base.'/Test.php';
        require_once($lib_file);
        $client = Test();
        $logFileName = "import-".date('Ymd').".log";
        $client->setLog("Sync Attribute ",null,$logFileName);
        $allCategories = array();
        $resultClient = $client->connect();
        $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $unset = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/import_product_settings/not_sync_attribute_properties', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $unsetProperties = array();

        $_countryCollectionFactory = $objectManager->create('Magento\Directory\Model\ResourceModel\Country\CollectionFactory');

        $collection = $_countryCollectionFactory->create()->loadByStore();

        $countryFactory = $objectManager->create('\Magento\Directory\Model\CountryFactory');

        $countryArray = array();

        foreach ($collection->getData() as $key => $country) {
           $country = $countryFactory->create()->loadByCode($country['country_id']);
           $countryName = $country->getName();
           $countryArray[$country['country_id']] = $countryName;
        }

        if (strlen($unset)){
            $unsetProperties = explode(',',$unset);
        }

        // $result = $resultClient->GetCustomersCSV(array('store_url' => $store_url, 'CUSTOMER_ID' => 16));
        // $resultClient = $resultClient->GetAttributes(array('store_url' => $store_url, 'entity_type_id' => 4));
        $bootstrap = Bootstrap::create(BP, $_SERVER);
        $objectManager = $bootstrap->getObjectManager();

        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $state = $objectManager->get('\Magento\Framework\App\State');
        $state->setAreaCode('frontend');

        $storeId = $storeManager->getStore()->getId();
        $paramObj = array('storeId' => $storeId);

        $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory');
        $customer = $customerFactory->create();
        $websiteId = $storeManager->getWebsite()->getWebsiteId();
        $total_obj = $this->helperData->getSoapQDOSObject(11, $paramObj);
        // echo count($total_obj); exit;
        //echo "<pre>"; print_r($total_obj); exit;
        $error = false;

        $int = 0;
        $created_count = 0;
        $newCustomers = 0; //newly added customers count
        $updateCustomers = 0; //updated customers count
        $t = 0;
        foreach($total_obj as $arr)
        {
            $id = 0; // set customer id as 0
            $int++;
            /**get website id of user**/
            if (isset($arr->IS_ACCOUNT)):
                $objectManagerNew = \Magento\Framework\App\ObjectManager::getInstance();
                $customerObj = $objectManagerNew->create('Magento\Customer\Model\ResourceModel\Customer\Collection');
                $customerObj = $customerObj->addAttributeToSelect('entity_id')
                                ->addAttributeToFilter('login_email',$arr->LOGIN_EMAIL)
                                ->addAttributeToFilter('is_account',1)
                                ->getFirstItem();
            else:
                $customerObj = $objectManager->create('Magento\Customer\Model\Customer');
                $customerObj->setWebsiteId($websiteId);
                $customerObj->loadByEmail($arr->EMAIL);
            endif;

            $customer_new = $objectManager->create('Magento\Customer\Model\Customer')->load($customerObj->getId());
            $id = $customer_new->getId();

            $new =0;
            /** when user does not exists and is a normal contact user do not create one in Magento
             *  as contact users are created in Magento and account users in qdos
             *  Account users will be updated/created and contact user just updated
             * */

            if (!$id && !$arr->IS_ACCOUNT) {
                continue;
            }

            if (!$id) {
                $customer->setEntityId((int)$arr->CUSTOMER_ID);
                $customer->setLastname("S");
                $customer->setEmail($arr->EMAIL);
                $customer->setWebsiteId($websiteId);
                $customer->setStore($storeManager->getStore());

                $groupId = $arr->GROUP_ID;
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $group = $objectManager->create(\Magento\Customer\Model\Group::class)->load($groupId);
                if($group->getId()){
                    $customer->setGroupId((int)$group->getId());
                }

                if ($arr->IS_ACCOUNT):
                    $customer->setPassword($arr->LOGIN_PASSWORD);
                else:
                    $customer->password_hash = md5($arr->PASSWORD_HASH);
                endif;
            }

            if ($arr->IS_ACTIVE):
                $customer->setIsActive(1);
            else:
                $customer->setIsActive(0);
            endif;

            $customer->setFirstname($arr->FIRSTNAME);
            if (!$arr->IS_ACCOUNT):
                $customer->setLastname("S");
            endif;

            if ($arr->IS_ACCOUNT):
                $customer->setEmail($arr->EMAIL);
            endif;

            if ($id):
                $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
                $subscriber = $subscriber->loadByEmail($arr->EMAIL);
                $newsletter = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);

                if($arr->GROUP_ID):
                    $groupId = $arr->GROUP_ID;
                    $group = $bootstrap->getObjectManager()->create(\Magento\Customer\Model\Group::class)->load($groupId);
                    if($group->getId()){
                        $customer->setGroupId((int)$group->getId());
                    }
                endif;

                if($subscriber->getId()) {
                    if ($arr->IS_SUBSCRIBED) {
                        $subscriber->setSubscriberStatus($newsletter::STATUS_SUBSCRIBED);
                    }else {
                        $subscriber->setSubscriberStatus($newsletter::STATUS_UNSUBSCRIBED);
                    }
                    $subscriber->save();
                }else{
                    $subscriber->setStatus($newsletter::STATUS_SUBSCRIBED);
                    $subscriber->setSubscriberEmail($arr->EMAIL);
                    $subscriber->setSubscriberConfirmCode($subscriber->RandomSequence());
                    $subscriber->setName($arr->FIRSTNAME.' '.$arr->LASTNAME);
                    $subscriber->setStoreId($storeManager->getStore()->getId());
                    $subscriber->setCustomerId((int)$arr->CUSTOMER_ID);
                    try {
                        $subscriber->save();
                    }catch (Exception $e) {
                        Mage::logException($e->getMessage());
                    }
                }
            endif;

            // update only billing address
            if ($arr->BILL_ADDR_FLAG):
                $billingAddress = $customer->getPrimaryBillingAddress();
                if (!$billingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                    $billingAddress = $objectManager->create('Magento\Customer\Model\Address');
                }
                $regionId ='';
                $regions = $objectManager->create('Magento\Directory\Model\ResourceModel\Region\Collection')
                            ->addFieldToFilter('name', ['eq' => $arr->BILLING_REGION])
                            ->getFirstItem();
                if ($regions) {
                    foreach($regions as $region) {
                        $regionId = intval($region->getId());
                    }
                }
                $billingAddress->setFirstname($arr->BILLING_FIRSTNAME);
                $billingAddress->setLastname($arr->BILLING_LASTNAME);
                $billingAddress->setLastname('S');
                $billingAddress->setCity($arr->BILLING_CITY);
                $billingAddress->setRegion($arr->BILLING_REGION);
                if (isset($regionId)) {
                    $billingAddress->setRegionId($regionId);
                }

                // temp code
                if(empty($arr->BILLING_COUNTRY) || empty($arr->BILLING_CITY) || empty($arr->BILLING_REGION) || empty($arr->BILLING_POSTCODE) || empty($arr->BILLING_TELEPHONE)){
                    continue;
                }
                
                // $billingCountryId = $this->getCountryCode($arr->BILLING_COUNTRY);
                $billingCountryId = array_search($arr->BILLING_COUNTRY, $countryArray);
                $billingAddress->setCountryId($billingCountryId);
                $billingAddress->setPostcode($arr->BILLING_POSTCODE);
                if ($arr->BILLING_STREET2!='') {
                    $billingAddress->setStreet(array($arr->BILLING_STREET1, $arr->BILLING_STREET2));
                } else {
                    $billingAddress->setStreet(array($arr->BILLING_STREET1));
                }
                if (isset($arr->BILLING_TELEPHONE)) {
                    $billingAddress->setTelephone($arr->BILLING_TELEPHONE);
                }
                if (isset($arr->BILLING_FAX)) {
                    $billingAddress->setFax($arr->BILLING_FAX);
                }
                if (isset($arr->BILLING_COMPANY)) {
                    $billingAddress->setCompany($arr->BILLING_COMPANY);
                }
                if (!$billingAddress->getId()) {
                    $billingAddress->setIsDefaultBilling(true);
                    if ($customer->getDefaultBilling()) {
                        $customer->setData('default_billing', '');
                    }
                    $customer->addAddress($billingAddress);
                }
            endif;

            //Update only shipping address
            if($arr->SHIP_ADDR_FLAG):
                $shippingAddress = $customer->getPrimaryShippingAddress();
                if (!$shippingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                    $shippingAddress = $objectManager->create('Magento\Customer\Model\Address');
                }

                $regionId = '';
                $regions = $objectManager->create('Magento\Directory\Model\ResourceModel\Region\Collection')
                            ->addFieldToFilter('name', ['eq' => $arr->SHIPPING_REGION])
                            ->getFirstItem();

                if ($regions) foreach($regions as $region) {
                    $regionId = intval($region->getId());
                }

                //temp
                if(empty($arr->SHIPPING_COUNTRY) || empty($arr->SHIPPING_CITY) || empty($arr->SHIPPING_REGION) || empty($arr->SHIPPING_POSTCODE) || empty($arr->SHIPPING_TELEPHONE)){
                    continue;
                }

                $shippingAddress->setFirstname($arr->SHIPPING_FIRSTNAME);
                // $shippingAddress->setLastname($arr->SHIPPING_LASTNAME);
                $shippingAddress->setLastname('s');
                $shippingAddress->setCity($arr->SHIPPING_CITY);

                $shippingAddress->setRegion($arr->SHIPPING_REGION);
                if (isset($regionId)) {
                    $shippingAddress->setRegionId($regionId);
                }

                // $shippingCountryId = $this->getCountryCode($arr->SHIPPING_COUNTRY);
                //$shippingAddress->setCountryId($shippingCountryId);

                $shippingCountryId = array_search($arr->SHIPPING_COUNTRY, $countryArray);
                $shippingAddress->setCountryId($shippingCountryId);
                $shippingAddress->setPostcode($arr->SHIPPING_POSTCODE);

                if ($arr->SHIPPING_STREET2!='') {
                    $shippingAddress->setStreet(array($arr->SHIPPING_STREET1, $arr->SHIPPING_STREET2));
                }else {
                    $shippingAddress->setStreet(array($arr->SHIPPING_STREET1));
                }
                if (isset($arr->SHIPPING_TELEPHONE)) {
                    $shippingAddress->setTelephone($arr->SHIPPING_TELEPHONE);
                }
                if (isset($arr->SHIPPING_FAX)) {
                    $shippingAddress->setFax($arr->SHIPPING_FAX);
                }
                if (isset($arr->SHIPPING_COMPANY)) {
                    $shippingAddress->setCompany($arr->SHIPPING_COMPANY);
                }
                if (!$shippingAddress->getId()) {
                    $shippingAddress->setIsDefaultShipping(true);
                    $customer->addAddress($shippingAddress);
                }
            endif;
            //End handling shipping address

            try{
                if(!$customer->save()){
                }else{
                    $customerNew = $this->customerModel->load($arr->CUSTOMER_ID);
                    $customerData = $customerNew->getDataModel();
                    if ($arr->TRADING_NAME):
                        $customerData->setCustomAttribute('tradin_name',$arr->TRADING_NAME);
                    endif;

                    if ($arr->IS_ACCOUNT):
                        $customerData->setCustomAttribute('login_email',$arr->LOGIN_EMAIL);
                        $customerData->setCustomAttribute('is_account',$arr->IS_ACCOUNT);
                    endif;
                    $customerNew->updateData($customerData);
                    $customerResource = $this->customerResourceFactory->create();
                    $customerResource->saveAttribute($customerNew, 'login_email');
                    $customerResource->saveAttribute($customerNew, 'tradin_name');
                    $customerResource->saveAttribute($customerNew, 'is_account');
                }
                $customer->save();
            } catch(\Exception $e){
                $errorMessage = $e->getMessage();
                // $this->_messageManager->addError($errorMessage);
                //$client->setLog("error: " . $errorMessage,null,$logFileName);
            }
        }
        return;
    }


    /***
     * Get the website id from website name
     */
    protected function _getWebsiteId($websiteName)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $website = $objectManager->create("\Magento\Store\Model\ResourceModel\Website\Collection")
            //->getCollection()
            ->addFieldToFilter('name',$websiteName)
            ->getFirstItem();

        if($website) {
            return $website->getId();
        } else {
            return 1;
        }

    }


    /**
     * Ravi Mule
     * sync customers from Qdos to Magento - Overidden method for CS instance of handling account users
     */
    public function syncCustomers($storeId = '')
    {

        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base.'/Test.php';
        require_once($lib_file);
        $client = Test();
        $logFileName = "import-".date('Ymd').".log";
        $client->setLog("Sync Customer ",null,$logFileName);
        $allCategories = array();
        $resultClient = $client->connect();

        /** if the customer sync is in progress than disable customer export */
        $client->setLog('sync_customer_process',null, $logFileName);
        $client->setLog('SYNC CUSTOMER INITIATED',null,"qdos-sync-customer-".date('Ymd').".log",true);
        $logMsg = array();
        $logMsg[] = "SYNC CUSTOMER INITIATED";
        $start_time = date('Y-m-d H:i:s');
        /**Model to log the entries into activity_log table**/
        $logModel = $objectManager->create("\Qdos\QdosSync\Model\Log");

        $logModel->setStartTime($start_time)
            ->setEndTime(1)
            ->setStatus(\Qdos\Sync\Model\Sync::LOG_PENDING)
            ->setIpAddress($_SERVER['REMOTE_ADDR'])
            ->setActivityType('customer')
            ->save();


        try {

            $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $unset = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/import_product_settings/not_sync_attribute_properties', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $unsetProperties = array();

            $_countryCollectionFactory = $objectManager->create('Magento\Directory\Model\ResourceModel\Country\CollectionFactory');

            $collection = $_countryCollectionFactory->create()->loadByStore();

            $countryFactory = $objectManager->create('\Magento\Directory\Model\CountryFactory');

            $countryArray = array();

            foreach ($collection->getData() as $key => $country) {
               $country = $countryFactory->create()->loadByCode($country['country_id']);
               $countryName = $country->getName();
               $countryArray[$country['country_id']] = $countryName;
            }

            if (strlen($unset)){
                $unsetProperties = explode(',',$unset);
            }

            $bootstrap = Bootstrap::create(BP, $_SERVER);
            $objectManager = $bootstrap->getObjectManager();

            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $state = $objectManager->get('\Magento\Framework\App\State');
            $state->setAreaCode('frontend');

            $storeId = $storeManager->getStore()->getId();
            $paramObj = array('storeId' => $storeId);

            $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory');
            $customer = $customerFactory->create();
            $websiteId = $storeManager->getWebsite()->getWebsiteId();
            
            $int        = 0;
            $created_count  = 0;
            $newCustomers   = 0; //newly added customers count
            $updateCustomers = 0; //updated customers count
            $paramObj = array('storeId' => $storeId);
            // sync code modified for new GetAccounts WS for CS
            $total_obj = $this->helperData->getSoapQDOSObject(11, $paramObj);

            $client->setLog('Total('.count($total_obj).') Customer will be added ',null,"qdos-sync-customer-".date('Ymd').".log",true);

            $logMsg[] = "Total Customer Fetched : ".count($total_obj);

            foreach($total_obj as $arr)
            {
                //print_r($arr);exit;
                //$client->setLog($arr,false,'customer_sync_data.log');
                //echo "<pre>";print_r($arr);exit;
                $id = 0; // set customer id as 0
                $int++;
                $websiteIdCustomer = $this->_getWebsiteId($arr->WEBSITE);

                /**get website id of user**/
                if (isset($arr->IS_ACCOUNT)):
                    $objectManagerNew = \Magento\Framework\App\ObjectManager::getInstance();
                    $customerObj = $objectManagerNew->create('Magento\Customer\Model\ResourceModel\Customer\Collection');
                    $customerObj = $customerObj->addAttributeToSelect('entity_id')
                                    ->addAttributeToFilter('login_email',$arr->LOGIN_EMAIL)
                                    ->addAttributeToFilter('is_account',1)
                                    ->getFirstItem();

                    $client->setLog('In If '.$arr->CUSTOMER_ID,false,'validcustomer.log');
                    $client->setLog('query  '.$customerObj->getSelect(),false,'validcustomer.log');
                else:
                    $customerObj = $objectManager->create('Magento\Customer\Model\Customer');
                    $customerObj->setWebsiteId($websiteId);
                    $customerObj->loadByEmail($arr->EMAIL);
                    $client->setLog('In Else '.$arr->CUSTOMER_ID,false,'validcustomer.log');
                endif;

                $customer_new = $objectManager->create('Magento\Customer\Model\Customer')->load($customerObj->getId());
                $id = $customer_new->getId();

                $client->setLog('LOGIN_EMAIL:'.$arr->LOGIN_EMAIL,false,'customerid.log');
                $client->setLog('EMAIL:'.$arr->EMAIL,false,'customerid.log');
                $client->setLog('CUSTOMER ID: '.$customer->getId(),false,'customerid.log');
                $client->setLog('WEBSITE ID:'.$websiteIdCustomer,false,'customerid.log');

                $new =0;
                /** when user does not exists and is a normal contact user do not create one in Magento
                 *  as contact users are created in Magento and account users in qdos
                 *  Account users will be updated/created and contact user just updated
                 * */

                if (!$id && !$arr->IS_ACCOUNT) {
                    continue;
                }

                if (!$id) {
                    //if it is a new user
                    $new = 1;
                    $customer->setEntityId((int)$arr->CUSTOMER_ID);
                    $customer->setLastname("S");
                    
                    // $websiteIdCustomer = $this->_getWebsiteId($arr->WEBSITE);
                   
                    $email = ($arr->EMAIL=='')?('email_'.$int.'@gmail.com'):($arr->EMAIL); //testing
                    $customer->setEmail($email);
                    //$customer->setWebsiteId($websiteId);
                    $customer->setWebsiteId($websiteIdCustomer); //for login into frontend
                    $customer->setStore($storeManager->getStore());

                    //$groupId = 3;//(int)$arr->CUSTOMER_GROUP_ID;
                    $groupId = $arr->GROUP_ID;
                    /*end of modified code*/
                    $group = $objectManager->create(\Magento\Customer\Model\Group::class)->load($groupId);
                    if($group->getId()){
                        $customer->setGroupId((int)$group->getId());
                    }
                    /**password to only be updated on create customer @Huzefa M**/
                    if ($arr->IS_ACCOUNT):
                        $customer->setPassword($arr->LOGIN_PASSWORD); //for account users
                    else:
                        $customer->password_hash = md5($arr->PASSWORD_HASH); //for normal users
                    endif;
                    /**end of password code**/
                    $customer->lastname = $arr->LASTNAME;
                }


                /* code for new and existing users*/

                // new fields added on 9th Sept '14 by huzefa
                //if ($arr->IS_ACCOUNT)  $customer
                if ($arr->TRADING_NAME):
                    $customer->setTradinName($arr->TRADING_NAME);
                endif;
                //end of code
                /**code to check active **/
                if ($arr->IS_ACTIVE):
                    $customer->setIsActive(1);
                else:
                    $customer->setIsActive(0);
                endif;

                $customer->setFirstname($arr->FIRSTNAME);
                if (!$arr->IS_ACCOUNT):
                    $customer->setLastname("S");
                endif;

                /*new code added for account users added by Huzefa*/
                if ($arr->IS_ACCOUNT):
                    $email = ($arr->EMAIL=='')?('email_'.$int.'@gmail.com'):($arr->EMAIL);
                    $customer->setEmail($email); 
                    $customer->setIsAccount($arr->IS_ACCOUNT);
                    $customer->setLoginEmail($arr->LOGIN_EMAIL);
                else:
                    $customer->setIsAccount(0);
                endif;

                if ($id)://only for customers update
                    $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
                    $subscriber = $subscriber->loadByEmail($arr->EMAIL);
                    $newsletter = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);

                    if($arr->GROUP_ID):
                        $groupId = $arr->GROUP_ID;
                        $group = $bootstrap->getObjectManager()->create(\Magento\Customer\Model\Group::class)->load($groupId);
                        if($group->getId()){
                            $customer->setGroupId((int)$group->getId());
                        }
                    endif;

                    if($subscriber->getId()) {
                        if ($arr->IS_SUBSCRIBED) {
                            $subscriber->setSubscriberStatus($newsletter::STATUS_SUBSCRIBED);
                        }else {
                            $subscriber->setSubscriberStatus($newsletter::STATUS_UNSUBSCRIBED);
                        }
                        $subscriber->save();
                    }else{
                        $subscriber->setStatus($newsletter::STATUS_SUBSCRIBED);
                        $subscriber->setSubscriberEmail($arr->EMAIL);
                        $subscriber->setSubscriberConfirmCode($subscriber->RandomSequence());
                        $subscriber->setName($arr->FIRSTNAME.' '.$arr->LASTNAME);
                        $subscriber->setStoreId($storeManager->getStore()->getId());
                        $subscriber->setCustomerId((int)$arr->CUSTOMER_ID);
                        try {
                            $subscriber->save();
                        }catch (Exception $e) {
                            $client->setLog("$subscriber:" .$e->getMessage());
                        }
                    }
                endif;

                // update only billing address
                if ($arr->BILL_ADDR_FLAG):
                    $billingAddress = $customer->getPrimaryBillingAddress();
                    if (!$billingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                        $billingAddress = $objectManager->create('Magento\Customer\Model\Address');
                    }
                    $regionId ='';
                    $regions = $objectManager->create('Magento\Directory\Model\ResourceModel\Region\Collection')
                                ->addFieldToFilter('name', ['eq' => $arr->BILLING_REGION])
                                ->getFirstItem();
                    if ($regions) {
                        foreach($regions as $region) {
                            $regionId = intval($region->getId());
                        }
                    }
                    $billingAddress->setFirstname($arr->BILLING_FIRSTNAME);
                    $billingAddress->setLastname($arr->BILLING_LASTNAME);
                    $billingAddress->setLastname('S');
                    $billingAddress->setCity($arr->BILLING_CITY);
                    $billingAddress->setRegion($arr->BILLING_REGION);
                    if (isset($regionId)) {
                        $billingAddress->setRegionId($regionId);
                    }

                    // temp code
                    if(empty($arr->BILLING_COUNTRY) || empty($arr->BILLING_CITY) || empty($arr->BILLING_REGION) || empty($arr->BILLING_POSTCODE) || empty($arr->BILLING_TELEPHONE)){
                        continue;
                    }
                    
                    // $billingCountryId = $this->getCountryCode($arr->BILLING_COUNTRY);
                    $billingCountryId = array_search($arr->BILLING_COUNTRY, $countryArray);
                    $billingAddress->setCountryId($billingCountryId);
                    $billingAddress->setPostcode($arr->BILLING_POSTCODE);
                    if ($arr->BILLING_STREET2!='') {
                        $billingAddress->setStreet(array($arr->BILLING_STREET1, $arr->BILLING_STREET2));
                    } else {
                        $billingAddress->setStreet(array($arr->BILLING_STREET1));
                    }
                    if (isset($arr->BILLING_TELEPHONE)) {
                        $billingAddress->setTelephone($arr->BILLING_TELEPHONE);
                    }
                    if (isset($arr->BILLING_FAX)) {
                        $billingAddress->setFax($arr->BILLING_FAX);
                    }
                    if (isset($arr->BILLING_COMPANY)) {
                        $billingAddress->setCompany($arr->BILLING_COMPANY);
                    }
                    if (!$billingAddress->getId()) {
                        $billingAddress->setIsDefaultBilling(true);
                        if ($customer->getDefaultBilling()) {
                            $customer->setData('default_billing', '');
                        }
                        $customer->addAddress($billingAddress);
                    }
                endif;

                //Update only shipping address
                if($arr->SHIP_ADDR_FLAG):
                    $shippingAddress = $customer->getPrimaryShippingAddress();
                    if (!$shippingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                        $shippingAddress = $objectManager->create('Magento\Customer\Model\Address');
                    }

                    $regionId = '';
                    $regions = $objectManager->create('Magento\Directory\Model\ResourceModel\Region\Collection')
                                ->addFieldToFilter('name', ['eq' => $arr->SHIPPING_REGION])
                                ->getFirstItem();

                    if ($regions) foreach($regions as $region) {
                        $regionId = intval($region->getId());
                    }

                    //temp
                    if(empty($arr->SHIPPING_COUNTRY) || empty($arr->SHIPPING_CITY) || empty($arr->SHIPPING_REGION) || empty($arr->SHIPPING_POSTCODE) || empty($arr->SHIPPING_TELEPHONE)){
                        continue;
                    }

                    $shippingAddress->setFirstname($arr->SHIPPING_FIRSTNAME);
                    // $shippingAddress->setLastname($arr->SHIPPING_LASTNAME);
                    $shippingAddress->setLastname('s');
                    $shippingAddress->setCity($arr->SHIPPING_CITY);

                    $shippingAddress->setRegion($arr->SHIPPING_REGION);
                    if (isset($regionId)) {
                        $shippingAddress->setRegionId($regionId);
                    }

                    // $shippingCountryId = $this->getCountryCode($arr->SHIPPING_COUNTRY);
                    //$shippingAddress->setCountryId($shippingCountryId);

                    $shippingCountryId = array_search($arr->SHIPPING_COUNTRY, $countryArray);
                    $shippingAddress->setCountryId($shippingCountryId);
                    $shippingAddress->setPostcode($arr->SHIPPING_POSTCODE);

                    if ($arr->SHIPPING_STREET2!='') {
                        $shippingAddress->setStreet(array($arr->SHIPPING_STREET1, $arr->SHIPPING_STREET2));
                    }else {
                        $shippingAddress->setStreet(array($arr->SHIPPING_STREET1));
                    }
                    if (isset($arr->SHIPPING_TELEPHONE)) {
                        $shippingAddress->setTelephone($arr->SHIPPING_TELEPHONE);
                    }
                    if (isset($arr->SHIPPING_FAX)) {
                        $shippingAddress->setFax($arr->SHIPPING_FAX);
                    }
                    if (isset($arr->SHIPPING_COMPANY)) {
                        $shippingAddress->setCompany($arr->SHIPPING_COMPANY);
                    }
                    if (!$shippingAddress->getId()) {
                        $shippingAddress->setIsDefaultShipping(true);
                        $customer->addAddress($shippingAddress);
                    }
                endif;
                //End handling shipping address
           

                try{
                    if(!$customer->save()){
                        $client->setLog("An error occured while saving customer" . $customer->firstname." ".$customer->lastname,false,'customer_sync_error.log');
                    }else{
                        $client->setLog("Imported Successfully-Customer with ID : ".$customer->getId(),false,'customer_sync_success.log');
                        if ($new) {
                            $logMsg[] = "Imported Successfully-Customer with ID : ".$customer->getId();
                            //send new account email to customer
                           // $customer->sendNewAccountEmail();
                            $newCustomers++;
                        }
                        else {
                            $logMsg[] = "Updated Successfully-Customer with ID : ".$customer->getId();
                            $updateCustomers++;
                        }
                        $created_count++;

                        $customerNew = $this->customerModel->load($arr->CUSTOMER_ID);
                        $customerData = $customerNew->getDataModel();
                        if ($arr->TRADING_NAME):
                            $customerData->setCustomAttribute('tradin_name',$arr->TRADING_NAME);
                        endif;

                        if ($arr->IS_ACCOUNT):
                            $customerData->setCustomAttribute('login_email',$arr->LOGIN_EMAIL);
                            $customerData->setCustomAttribute('is_account',$arr->IS_ACCOUNT);
                        endif;
                        $customerNew->updateData($customerData);
                        $customerResource = $this->customerResourceFactory->create();
                        $customerResource->saveAttribute($customerNew, 'login_email');
                        $customerResource->saveAttribute($customerNew, 'tradin_name');
                        $customerResource->saveAttribute($customerNew, 'is_account');
                    }
                    $customer->save();
                } catch (\Exception $ex) {
                    $client->setLog('Exception (1) - For Customer ('.$customer->getId().'):'. $ex->getMessage(),null,"qdos-sync-customer-".date('Ymd').".log",true);
                    $client->setLog($ex->getMessage(), null, "qdos-sync-customer-".date('Ymd').".log",true);
                    $logMsg[] = "Exception While Importing Customer with ID : ".$customer->getId().". Exception : <b style='color: red'>".$ex->getMessage()."</b>";
                }
                $bool = true;
            }
            $client->setLog('Total('.$created_count.') Customer added ',null,"qdos-sync-customer-".date('Ymd').".log",true);
            $client->setLog('SYNC CUSTOMER FINISHED',null,"qdos-sync-customer-".date('Ymd').".log",true);

            $logMsg[] = "Total Customer Imported : ".$created_count;
            $logMsg[] = "Total Customer Added : ".$newCustomers;
            $logMsg[] = "Total Customer Updated : ".$updateCustomers;
 
            $status = \Qdos\Sync\Model\Sync::LOG_PARTIAL;

            if($created_count == count($total_obj)) {
                $status = \Qdos\Sync\Model\Sync::LOG_SUCCESS;
            }
            $status = \Qdos\Sync\Model\Sync::LOG_SUCCESS;

            $logMsg[] = "SYNC CUSTOMER COMPLETED";


            if ($bool) {
                //$message = $this->__('Customer were synchronized success.');
                $client->setLog('Customer were synchronized success.', null, "qdos-sync-customer-".date('Ymd').".log");
                //$this->_getSession()->addSuccess($message);
            } else {
                //$this->_getSession()->addError($this->__('Can not synchronize customer.'));
                $client->setLog('Can not synchronize some customers.', null, "qdos-sync-customer-".date('Ymd').".log",true);
            }

        } catch (Exception $ex) {
            $client->setLog('Exception (2):'. $ex->getMessage(),null,"qdos-sync-customer-".date('Ymd').".log",true);
            $client->setLog($ex->getMessage(), null, "qdos-sync-customer-".date('Ymd').".log",true);
            $logMsg[] = "Exception : <b style='color:red'>".$ex->getMessage()."</b>";

        }
        $logModel->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus($status)
            ->setDescription(implode('<br />',$logMsg))
            ->save();

        $client->setLog('SYNC CUSTOMER FINISHED-end of time',null,"qdos-sync-customer-".date('Ymd').".log",true);
        $client->setLog($ex->getMessage(),null,"qdos-sync-customer-".date('Ymd').".log",true);

    }

    /**
     * create a shipping address from billing address
     * @author Huzefa Madraswala
     * @param Mage_Customer_Model_Customer $customer
     * @param int $address_id
     * @return bool
     */
    public function createShippingAddress(){
        // need to convert into magento 2
        Mage::log($customerId,false,'create_address.log');
        Mage::log($addressId,false,'create_address.log');
        $addressToCopy = Mage::getModel('customer/address')->load($addressId);

        $address = Mage::getModel('customer/address');
        $address->setCustomerId($customerId)
            ->setFirstname($addressToCopy->getFirstname())
            ->setMiddleName($addressToCopy->getMiddlename())
            ->setLastname($addressToCopy->getLastname())
            ->setCountryId($addressToCopy->getCountryId())
            ->setRegionId($addressToCopy->getRegionId())
            ->setRegion($addressToCopy->getRegion())//state/province, only needed if the country is USA
            ->setPostcode($addressToCopy->getPostcode())
            ->setCity($addressToCopy->getCity())
            ->setStreet($addressToCopy->getStreet())
            ->setTelephone($addressToCopy->getTelephone())
            ->setFax($addressToCopy->getFax())
            ->setCompany($addressToCopy->getCompany())
            ->setStreet($addressToCopy->getStreet())
            ->setIsDefaultShipping('1')
           ->setSaveInAddressBook('1');

        try{
            $address->save();
        }
        catch (Exception $e) {
           // Mage::logException($e->getMessage());
            Mage::log($e->getMessage(),false,'customer_create_ship.log');
            return false;
        }

        return true;
    }
}