<?php
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
            $cntif=0;$cntelse=0;
            foreach($total_obj as $arr)
            {
                //print_r($arr);exit;
                //$client->setLog($arr,false,'customer_sync_data.log');
                //echo "<pre>";print_r($arr);exit;
                $id = 0; // set customer id as 0
                $int++;
                $websiteIdCustomer = $this->_getWebsiteId($arr->WEBSITE);

                /**get website id of user**/
                if (!empty($arr->IS_ACCOUNT)):
                    $objectManagerNew = \Magento\Framework\App\ObjectManager::getInstance();
                    $customerObj = $objectManagerNew->create('Magento\Customer\Model\ResourceModel\Customer\Collection');
                    $customerObj = $customerObj->addAttributeToSelect('entity_id')
                                    ->addAttributeToFilter('login_email',$arr->LOGIN_EMAIL)
                                    ->addAttributeToFilter('is_account',1)
                                    ->getFirstItem();

                    $client->setLog('In If '.$arr->CUSTOMER_ID,false,'validcustomer.log');
                    $client->setLog('query  '.$customerObj->getSelect(),false,'validcustomer.log');
                     $client->setLog('if  '.$cntif++,false,'validcustomercount.log');
                else:
                    $customerObj = $objectManager->create('Magento\Customer\Model\Customer');
                    $customerObj->setWebsiteId($websiteId);
                    $customerObj->loadByEmail($arr->EMAIL);
                    $client->setLog('In Else '.$arr->CUSTOMER_ID,false,'validcustomer.log');
                    $client->setLog('else  '.$cntelse++,false,'validcustomercount.log');

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
                       // $customer->password_hash = md5($arr->PASSWORD_HASH); //for normal users
                    	 $customer->setPassword("12345678");
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

                if(!$id)
                {
                	$email = ($arr->EMAIL=='')?('email_'.$int.'@gmail.com'):($arr->EMAIL);
                	 $customer->setEmail($email);
                }

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

                $customer->save();
 $client->setLog("billing flag". $arr->BILL_ADDR_FLAG."<br> shipping".$arr->SHIP_ADDR_FLAG,false,'flagbilling.log');
                // update only billing address
                if ($arr->BILL_ADDR_FLAG):
                    $billingAddress = $customer->getPrimaryBillingAddress();

                    print_r($customer->getPrimaryBillingAddress());
                    if (!$billingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                        $billingAddress = $objectManager->create('Magento\Customer\Model\Address');

                        $addresss = $objectManager->get('\Magento\Customer\Model\AddressFactory');

						$billingAddress = $addresss->create();						
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
                    $billingAddress->setCustomerId($arr->CUSTOMER_ID);	
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
                    print_r($customer->getBillingAddress()->getData());
                    exit;
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
                     $shippingAddress->setCustomerId($arr->CUSTOMER_ID);
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
               
            }
             $bool = true;
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
       // $client->setLog($ex->getMessage(),null,"qdos-sync-customer-".date('Ymd').".log",true);
