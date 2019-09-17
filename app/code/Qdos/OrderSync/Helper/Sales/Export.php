<?php

namespace Qdos\OrderSync\Helper\Sales;
use Magento\Newsletter\Model\Subscriber;
class Export extends \Magento\Framework\App\Helper\AbstractHelper
{
   
	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Qdos\Sync\Helper\Api $helperData,
        \Qdos\Sync\Helper\Config $confighelperData,
        \Qdos\Sync\Helper\Customer $syncCustomer,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\catalog\Model\Product $product,
        \Magento\Sales\Model\Order\Item $orderItem
        
	) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->confighelperData = $confighelperData;
        $this->syncCustomer = $syncCustomer;
	    $this->directory_list = $directory_list;
        $this->_countryFactory = $countryFactory;
        $this->_coreSession = $coreSession;
        $this->resourceConnection = $resourceConnection;
        $this->_subscriber = $subscriber;
        $this->product = $product;
        $this->orderItem = $orderItem;
	}

    public function exportMultiOrders($orders, $storeId = 0, $logMsg = array()){
        $error = false;
        $base = $this->directory_list->getPath('lib_internal');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lib_file = $base.'/Test.php'; 
        require_once($lib_file);
        $client = Test();

        $resultClient = $client->connect();


        $logFileName = "order_generation_".date('Ymd').'.log';
        $orderArr = $incrementIds = array();
                
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getId();
        $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $hasMulti = $this->hasBatchFunction($resultClient);
        foreach ($orders as $order) {
            try{
                $orderData = $this->parse($order,!$hasMulti);
                $orderArr[] = $orderData;
                //echo "<pre>";print_r($orderArr);exit;
                $incrementIds[] = $order->getIncrementId();

                $logMsg[]="Order Data : ".json_encode($orderData);
                $logMsg[] = 'Send Order Success '.$orderData['INCREMENT_ID'];

                $result =  $objectManager->get('Qdos\OrderSync\Helper\Sales\Note')->exportNote($order, $storeId, $logMsg);
                if (!$hasMulti){
                    //$client->setLog("Store url Swapnil :".$store_url.' Order data Swapnil: '.json_encode($orderData),null,"OrderSync-".date('Ymd').".log");
                    $result = $resultClient->SendOrderCSV(array('store_url'=>$store_url, 'order'=>$orderData));
                    if (isset($result->outErrorMsg) && strlen($result->outErrorMsg)){
                        $error = true;
                        $logMsg[] = 'Order Errors: '.$result->outErrorMsg;
                    }
                    else
                    {
                        if (isset($saveVoucher) && !$saveVoucher){
                            $customer =  $objectManager->create('Magento\Customer\Model\Customer')->load($order->getCustomerId());
                            $this->_order = $order;
                            $this->exportCustomer($customer, $result->orderRowID,$order->getIncrementId(),$logMsg);
                        }
                        else{
                            $this->_coreSession->setOrderQdosId($result->orderRowID);
                        }
                        $logMsg[] = 'Send Order Success '.$orderData['INCREMENT_ID'];
                    }
                }
                unset($orderData);
            }catch(Exception $e) {
                $logMsg[] = 'Error in processing';
                $client->setLog('Order: '.$e->getMessage(),null,$logFileName);
                $logMsg[] = $this->addError('Order Errors: '.$e->getMessage());
                $error = true;
            }
        }       
        if ($hasMulti && count($orderArr) > 0){
            /*$orderArr[0]['HIDDEN_TAX_AMOUNT']=$orderArr[0]['DISCOUNT_TAX_COMPENSATION_AMOUNT'];
            //foreach ($orderArr as $key => $order) {
            foreach ($orderArr[0]['Items'] as $key1 => $value) {
                $orderArr[0]['Items'][$key1]['PRODUCT_OPTIONS'] =serialize($orderArr[0]['Items'][$key1]['PRODUCT_OPTIONS']);
            }*/
            foreach ($orderArr as $key => $order) {
                $orderArr[$key]['HIDDEN_TAX_AMOUNT']=$orderArr[$key]['DISCOUNT_TAX_COMPENSATION_AMOUNT'];
                foreach ($orderArr[$key]['Items'] as $key1 => $value) {
                    $orderArr[$key]['Items'][$key1]['PRODUCT_OPTIONS'] =serialize($orderArr[$key]['Items'][$key1]['PRODUCT_OPTIONS']);
                }
            }
            //$orderArr[0]['Items'][0]['PRODUCT_OPTIONS'] =serialize($orderArr[0]['Items'][0]['PRODUCT_OPTIONS']);
            //echo '<pre>';print_r($orderArr);exit;
            //$result = $resultClient->SaveOrderBatchCSV(array('store_url'=>$store_url, 'details'=>json_encode($orderArr)));
            $result = $resultClient->SaveOrderBatchCSV(array('store_url'=>$store_url, 'details'=>$orderArr));
            $client->setLog("Processed order by Ravi ::". json_encode($result),null,"RaviOrderSync-".date('Ymd').".log");
                if ($result->outErrorMsg && strlen($result->outErrorMsg) > 0) {
                    $error = true;
                    $logMsg[] = 'Error in processing';
                    $logMsg[] = $this->addError($result->outErrorMsg);
                    $client->setLog("Error in processing Swapnil :".$result->outErrorMsg ,null,$logFileName);
                } elseif (isset($result->SaveOrderBatchCSVResult) && $result->SaveOrderBatchCSVResult) {
                    foreach ($incrementIds as $incrementId){
                        $logMsg[] = 'Exported successful order #'.$incrementId;
                        $client->setLog('Swapnil Exported successful order #'.json_encode($result),null,"OrderSync-".date('Ymd').".log");
                    }
                }else{
                    $error = true;
                    foreach ($incrementIds as $incrementId){
                        $client->setLog('Swapnil Exported Failed order #'.$incrementId,null,"OrderSync-".date('Ymd').".log");
                    }
                }
        }
        
        return array(!$error,$logMsg);
    }// end of function sync customer

    protected function getDeliveryComment($order){
        $commentDetails = '';
        $comment = $this->_coreSession->getQdosComment();
        if ($this->confighelperData->moduleIsExist('Idev', 'OneStepCheckout')) {
            if ($order->getOnestepcheckoutCustomercomment()) {
                $commentDetails = $order->getOnestepcheckoutCustomercomment();
            }
        } else {
            if (strlen($comment)) {
                $commentDetails = $comment;
                $this->_coreSession->setQdosComment('');
            } elseif (strlen($order->getSafedropComment())) {
                $commentDetails = $order->getSafedropComment();
            } elseif (!$this->confighelperData->tableColumnExist('sales_order','safedrop_comment')){
                $history = array();
                foreach ($order->getAllStatusHistory() as $orderComment) {
                    $history[] = $orderComment->getComment();
                }
                $commentDetails = implode(', ', $history);
            }
        }
        return $commentDetails;
    }

    protected function hasBatchFunction($resultClient){
        $soapFunctions = $resultClient->getTypes();
        $params = $this->confighelperData->getSoapParams($soapFunctions, 'SaveOrderBatchCSV', 'details');
        return is_array($params) and count($params) > 0;
    }

    public function setDataCustomerNonRegistered($order,$orderId,$increment_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $websiteId = $storeManager->getStore()->getWebsiteId();
        $billingAddress = $order->getBillingAddress();
        //check subscribed
        $subscribed     = $this->_subscriber->loadByEmail($billingAddress->getEmail());

        $session = $this->_coreSession->getGosSubscribe();
        if (!is_null($session)){
           $isSubscribed = $session;
        }else{
           $isSubscribed   = is_object($subscribed) && $subscribed->getId()?$subscribed->getSubscriberStatus():0;
        }

        $data           = $this->syncCustomer->getAllCustomerAttribute();
        $data['WEBSITE']                   = $websiteId;
        //$data['EMAIL']                     = $order->getCustomerEmail(); //commented by Shailendra Gupta on 21 Aug 2014 for pushing the email id
        $data['EMAIL']                     = $this->_getCustomerEmail($order);
        $data['GROUP_ID']                  = '0';
        $data['DISABLE_AUTO_GROUP_CHANGE'] = 0;
        $data['FIRSTNAME']                 = $order->getCustomerFirstname();
        $data['LASTNAME']                  = $order->getCustomerLastname();
        $data['PASSWORD_HASH']             = md5('qdos');
        $data['CREATED_IN']                = $storeManager->getStore()->getName();
        $data['IS_SUBSCRIBED']             = $isSubscribed?$isSubscribed:0;
        $data['GROUP']                     = '';
        $data['CUSTOMER_GROUP_ID']         = 0;
        $data['CUSTOMER_ID']               = 1;
        $data['ORDER_ID']                  = $orderId;
        $data['STYLIST_ID']                = 0;
        $data['INCREMENT_ID']              = $increment_id;

        //billing Address
        $data['BILL_ADDR_FLAG'] = 1;
        //Added by Shailendra Gupta on 15 sept 2014 for handling new parameters in the webservice
        $data['ADDITIONAL_PARAMETERS'] = '';
        //End by Shailendra Gupta

        $data['BILLING_PREFIX']      = '';
        $data['BILLING_SUFFIX']      = '';
        $data['BILLING_FIRSTNAME']   = $billingAddress->getFirstname();
        $data['BILLING_MIDDLENAME']  = $billingAddress->getMiddlename();
        $data['BILLING_LASTNAME']    = $billingAddress->getLastname();
        $data['BILLING_STREET_FULL'] = implode(' ',$billingAddress->getStreet());
        $data['BILLING_STREET1']     = implode(' ',$billingAddress->getStreet());
        $data['BILLING_STREET2']     = '';
        $data['BILLING_STREET3']     = '';
        $data['BILLING_STREET4']     = '';
        $data['BILLING_STREET5']     = '';
        $data['BILLING_STREET6']     = '';
        $data['BILLING_STREET7']     = '';
        $data['BILLING_STREET8']     = '';
        $data['BILLING_CITY']        = $billingAddress->getCity();
        $data['BILLING_REGION']      = $billingAddress->getRegion();
        $data['BILLING_COUNTRY']     = $billingAddress->getCountry();
        $data['BILLING_POSTCODE']    = $billingAddress->getPostcode();
        $data['BILLING_TELEPHONE']   = $billingAddress->getTelephone();
        $data['BILLING_COMPANY']     = $billingAddress->getCompany();
        $data['BILLING_FAX']         = $billingAddress->getFax();

        //shipping Address
       if($order->getIsNotVirtual()){
            $shippingAddress              = $order->getShippingAddress();
            $data['SHIP_ADDR_FLAG']       = 1;
            $data['SHIPPING_PREFIX']      = '';
            $data['SHIPPING_SUFFIX']      = '';
            $data['SHIPPING_FIRSTNAME']   = $shippingAddress->getFirstname();
            $data['SHIPPING_MIDDLENAME']  = $shippingAddress->getMiddlename();
            $data['SHIPPING_LASTNAME']    = $shippingAddress->getLastname();
            $data['SHIPPING_STREET_FULL'] = implode(' ',$shippingAddress->getStreet());
            $data['SHIPPING_STREET1']     = implode(' ',$shippingAddress->getStreet());
            $data['SHIPPING_STREET2']     = '';
            $data['SHIPPING_STREET3']     = '';
            $data['SHIPPING_STREET4']     = '';
            $data['SHIPPING_STREET5']     = '';
            $data['SHIPPING_STREET6']     = '';
            $data['SHIPPING_STREET7']     = '';
            $data['SHIPPING_STREET8']     = '';
            $data['SHIPPING_CITY']        = $shippingAddress->getCity();
            $data['SHIPPING_REGION']      = $shippingAddress->getRegion();
            $data['SHIPPING_COUNTRY']     = $shippingAddress->getCountry();
            $data['SHIPPING_POSTCODE']    = $shippingAddress->getPostcode();
            $data['SHIPPING_TELEPHONE']   = $shippingAddress->getTelephone();
            $data['SHIPPING_COMPANY']     = $shippingAddress->getCompany();
            $data['SHIPPING_FAX']         = $shippingAddress->getFax();
       }else{
            $data['SHIP_ADDR_FLAG']       = 0;
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

    protected function parse($order, $oldService = false){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderData = $order->getData();

        // Set Not Invoice
        $orderData['BASE_DISCOUNT_INVOICED'] = isset($orderData['BASE_DISCOUNT_INVOICED'])
                ? $orderData['BASE_DISCOUNT_INVOICED'] : 0;
        $orderData['BASE_SHIPPING_INVOICED'] = isset($orderData['BASE_SHIPPING_INVOICED'])
                ? $orderData['BASE_SHIPPING_INVOICED'] : 0;
        $orderData['BASE_SUBTOTAL_INVOICED'] = isset($orderData['BASE_SUBTOTAL_INVOICED'])
                ? $orderData['BASE_SUBTOTAL_INVOICED'] : 0;
        $orderData['BASE_TAX_INVOICED'] = isset($orderData['BASE_TAX_INVOICED']) ? $orderData['BASE_TAX_INVOICED'] : 0;
        $orderData['BASE_TOTAL_INVOICED'] = isset($orderData['BASE_TOTAL_INVOICED']) ? $orderData['BASE_TOTAL_INVOICED']
                : 0;
        $orderData['BASE_TOTAL_INVOICED_COST'] = isset($orderData['BASE_TOTAL_INVOICED_COST'])
                ? $orderData['BASE_TOTAL_INVOICED_COST'] : 0;
        $orderData['BASE_TOTAL_PAID'] = isset($orderData['BASE_TOTAL_PAID']) ? $orderData['BASE_TOTAL_PAID'] : 0;
        $orderData['DISCOUNT_INVOICED'] = isset($orderData['DISCOUNT_INVOICED']) ? $orderData['DISCOUNT_INVOICED'] : 0;
        $orderData['SHIPPING_INVOICED'] = isset($orderData['SHIPPING_INVOICED']) ? $orderData['SHIPPING_INVOICED'] : 0;
        $orderData['SUBTOTAL_INVOICED'] = isset($orderData['SUBTOTAL_INVOICED']) ? $orderData['SUBTOTAL_INVOICED'] : 0;
        $orderData['TAX_INVOICED'] = isset($orderData['TAX_INVOICED']) ? $orderData['TAX_INVOICED'] : 0;
        $orderData['TOTAL_INVOICED'] = isset($orderData['TOTAL_INVOICED']) ? $orderData['TOTAL_INVOICED'] : 0;
        $orderData['TOTAL_PAID'] = isset($orderData['TOTAL_PAID']) ? $orderData['TOTAL_PAID'] : 0;
        $orderData['EMAIL_SENT'] = isset($orderData['EMAIL_SENT']) ? $orderData['EMAIL_SENT'] : 0;
        $orderData['HIDDEN_TAX_INVOICED'] = isset($orderData['HIDDEN_TAX_INVOICED']) ? $orderData['HIDDEN_TAX_INVOICED']
                : 0;
        $orderData['BASE_HIDDEN_TAX_INVOICED'] = isset($orderData['BASE_HIDDEN_TAX_INVOICED'])
                ? $orderData['BASE_HIDDEN_TAX_INVOICED'] : 0;
        $orderData['BASE_CUSTOMER_BALANCE_INVOICED'] = isset($orderData['BASE_CUSTOMER_BALANCE_INVOICED'])
                ? $orderData['BASE_CUSTOMER_BALANCE_INVOICED'] : 0;
        $orderData['CUSTOMER_BALANCE_INVOICED'] = isset($orderData['CUSTOMER_BALANCE_INVOICED'])
                ? $orderData['CUSTOMER_BALANCE_INVOICED'] : 0;


        if (!isset($orderData['shipping_address_id'])) {
            $orderData['shipping_address_id'] = 0;
        }
        if (is_null($orderData['customer_prefix'])) {
            $orderData['customer_prefix'] = '';
        }
        if (is_null($orderData['customer_middlename'])) {
            $orderData['customer_middlename'] = '';
        }
        if (is_null($orderData['customer_suffix'])) {
            $orderData['customer_suffix'] = '';
        }
        if (is_null($orderData['customer_dob'])) {
            $orderData['customer_dob'] = time();
        } elseif (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $orderData['customer_dob'])) {
            $orderData['customer_dob'] = strtotime($orderData['customer_dob']);
        }
        if (!isset($orderData['is_multi_payment']) ||
            (isset($orderData['is_multi_payment']) && is_null($orderData['is_multi_payment']) && $orderData['is_multi_payment'] == '')
        ) {
            $orderData['is_multi_payment'] = 0;
        }
        if (is_null($orderData['customer_taxvat'])) {
            $orderData['customer_taxvat'] = '';
        }
        if (!isset($orderData['customer_tax_class_id']) ||
            (isset($orderData['customer_tax_class_id']) && is_null($orderData['customer_tax_class_id']))
        ) {
            $orderData['customer_tax_class_id'] = 0;
        }
        if (is_null($orderData['customer_gender'])) {
            $orderData['customer_gender'] = 0;
        }
        if (is_null($orderData['gift_message_id'])) {
            $orderData['gift_message_id'] = 0;
        }
        if (isset($orderData['gift_message_available']) && is_null($orderData['gift_message_available'])) {
            $orderData['gift_message_available'] = 0;
        }
        if (!isset($orderData['shipping_rate']) || (isset($orderData['shipping_rate']) && is_null($orderData['shipping_rate']))) {
            $orderData['shipping_rate'] = 1;
        }
        if (!isset($orderData['custbalance_amount']) || (isset($orderData['custbalance_amount']) && is_null($orderData['custbalance_amount']))) {
            $orderData['custbalance_amount'] = 0;
        }
        if (!isset($orderData['base_custbalance_amount']) || (isset($orderData['base_custbalance_amount']) && is_null($orderData['base_custbalance_amount']))) {
            $orderData['base_custbalance_amount'] = 0;
        }
        if (!isset($orderData['customer_note']) || (isset($orderData['customer_note']) && is_null($orderData['customer_note']))) {
            $orderData['customer_note'] = '';
        }

        if (!isset($orderData['customer_note_notify']) || (isset($orderData['customer_note_notify']) && is_null($orderData['customer_note_notify']))) {
            $orderData['customer_note_notify'] = '';
        }     
        if (!isset($orderData['customer_is_guest']) || (isset($orderData['customer_is_guest']) && is_null($orderData['customer_is_guest']))) {
            $orderData['customer_is_guest'] = '';
        }

        if (!isset($orderData['coupon_code']) || is_null($orderData['coupon_code'])) {
            $orderData['coupon_code'] = '';
        }
        if (!isset($orderData['APPLIED_RULE_IDS']) || is_null($orderData['APPLIED_RULE_IDS'])) {
            $orderData['APPLIED_RULE_IDS'] = 0;
        }
        if (!isset($orderData['REMOTE_IP']) || is_null($orderData['REMOTE_IP'])) {
            $orderData['REMOTE_IP'] = '';
        }
        if (!isset($orderData['CUSTOMER_ID']) || is_null($orderData['CUSTOMER_ID'])) {
            $orderData['CUSTOMER_ID'] = 0;
        }
        $orderData['store_to_base_rate'] = (float)$orderData['store_to_base_rate'];
        $orderData['store_to_order_rate'] = (float)$orderData['store_to_order_rate'];
        $orderData['base_to_global_rate'] = (float)$orderData['base_to_global_rate'];
        $orderData['base_to_order_rate'] = (float)$orderData['base_to_order_rate'];
        $orderData['base_shipping_amount'] = (float)$orderData['base_shipping_amount'];
        unset($orderData['applied_taxes']);
        foreach ($orderData as $key => $value) {
            if (is_object($value)) unset($orderData[$key]);
            if (is_null($value)) unset($orderData[$key]);
        }
        //$orderData['BASE_SHIPPING_TAX_AMOUNT'] = (float)$orderData['base_shipping_amount'];
        $orderData = array_change_key_case($orderData, CASE_UPPER);

        $billing = $order->getBillingAddress()->getData();
        if (isset($billing['customer_address'])) unset($billing['customer_address']);
        $billing = array_change_key_case($billing, CASE_UPPER);
        //unset($billing['STORE_ID']);
        unset($billing['STORE_ID']);
        unset($billing['VAT_ID']);
        unset($billing['VAT_IS_VALID']);
        unset($billing['VAT_REQUEST_ID']);
        unset($billing['VAT_REQUEST_DATE']);
        unset($billing['VAT_REQUEST_SUCCESS']);
        if (is_null($billing['PREFIX'])) {
            $billing['PREFIX'] = '';
        }
        if (is_null($billing['MIDDLENAME'])) {
            $billing['MIDDLENAME'] = '';
        }
        if (is_null($billing['SUFFIX'])) {
            $billing['SUFFIX'] = '';
        }
        if (is_null($billing['COMPANY'])) {
            $billing['COMPANY'] = '';
        }
        if (is_null($billing['FAX'])) {
            $billing['FAX'] = '';
        }
        if (is_null($billing['REGION_ID'])) {
            $billing['REGION_ID'] = 0;
        }
        if (is_null($billing['EMAIL'])) {
            $billing['EMAIL'] = '';
        }
        if (is_null($billing['CUSTOMER_ID'])) {
            $billing['CUSTOMER_ID'] = 0;
        }
        if (is_null($billing['CUSTOMER_ADDRESS_ID'])) {
            $billing['CUSTOMER_ADDRESS_ID'] = 0;
        } else {
            $billing['CUSTOMER_ADDRESS_ID'] = (int)$billing['CUSTOMER_ADDRESS_ID'];
        }
        $billing['ADDRESS_TYP'] = $billing['ADDRESS_TYPE'];
        $billing['ORDER_ID'] = $orderData['ENTITY_ID'];
        $billing['COUNTRY_NAME'] = $this->_countryFactory->create()->loadByCode($billing['COUNTRY_ID'])->getName();
        $billing['COUNTRY_ID'] = 1;
        $orderData['BillingAddress'] = $billing;
        $orderData['CUSTOMER_EMAIL'] = $this->_getCustomerEmail($order);

        if ($order->getIsNotVirtual()) {
            $shipping = array_change_key_case($order->getShippingAddress()->getData(), CASE_UPPER);
        } else {
            $shipping = array_change_key_case($order->getBillingAddress()->getData(), CASE_UPPER);
            foreach ($shipping as $key => $value) {
                if (is_numeric($value)) {
                    if ($key == 'REGION_ID') $shipping[$key] = 0;
                } else {
                    $shipping[$key] = '';
                }
            }
            $shipping['ADDRESS_TYPE'] = 'shipping';
        }
        unset($shipping['STORE_ID']);
        unset($shipping['VAT_ID']);
        unset($shipping['VAT_IS_VALID']);
        unset($shipping['VAT_REQUEST_ID']);
        unset($shipping['VAT_REQUEST_DATE']);
        unset($shipping['VAT_REQUEST_SUCCESS']);
        if (is_null($shipping['PREFIX'])) {
            $shipping['PREFIX'] = '';
        }
        if (is_null($shipping['MIDDLENAME'])) {
            $shipping['MIDDLENAME'] = '';
        }
        if (is_null($shipping['SUFFIX'])) {
            $shipping['SUFFIX'] = '';
        }
        if (is_null($shipping['COMPANY'])) {
            $shipping['COMPANY'] = '';
        }
        if (is_null($shipping['FAX'])) {
            $shipping['FAX'] = '';
        }
        if (is_null($shipping['REGION_ID'])) {
            $shipping['REGION_ID'] = 0;
        }
        if (is_null($shipping['EMAIL'])) {
            $shipping['EMAIL'] = '';
        }
        if (is_null($shipping['CUSTOMER_ID'])) {
            $shipping['CUSTOMER_ID'] = 0;
        }
        if (is_null($shipping['CUSTOMER_ADDRESS_ID'])) {
            $shipping['CUSTOMER_ADDRESS_ID'] = 0;
        } else {
            $shipping['CUSTOMER_ADDRESS_ID'] = (int)$shipping['CUSTOMER_ADDRESS_ID'];
        }

        $shipping['ORDER_ID'] = $orderData['ENTITY_ID'];
        $shipping['ORDER_ID'] = $orderData['ENTITY_ID'];
        $shipping['COUNTRY_ID'] = $orderData['BillingAddress']['COUNTRY_ID'];
        $shipping['COUNTRY_NAME'] =  $orderData['BillingAddress']['COUNTRY_NAME'];
        // $shipping['COUNTRY_NAME'] = $this->_countryFactory->create()->loadByCode($shipping['COUNTRY_ID'])->getName();
        
        
        $orderData['ShippingAddress'] = $shipping;
        $orderData['ShippingMethod'] = $order->getShippingMethod();
        if (!isset($orderData['QUOTE_BASE_GRAND_TOTAL']) || is_null($orderData['QUOTE_BASE_GRAND_TOTAL'])) {
            $orderData['QUOTE_BASE_GRAND_TOTAL'] = 0;
        }
        $defaultCountry = $this->scopeConfig->getValue('general/store_information/country_id');
        if ($order->getShippingAddress()) {
            $orderData['INTERNATIONAL_SHIPPING'] = $order->getShippingAddress()->getCountry() == $defaultCountry ? 0
                    : 1;
        } else {
            $orderData['INTERNATIONAL_SHIPPING'] = 0;
        }

        $orderData['ORDER_STATUS'] = ($order->getStatus() != '') ? $order->getStatus() : 'pending';
        $orderData['BASE_SUBTOTAL_INCL_TAX'] = (isset($orderData['BASE_SUBTOTAL_INCL_TAX']) && $orderData['BASE_SUBTOTAL_INCL_TAX'] != "")
                ? $orderData['BASE_SUBTOTAL_INCL_TAX'] : 0;

        $fee = unserialize($order->getDetailsMultifees());
        $selection = 0;
        if (is_array($fee) and count($fee) > 0) {
            foreach ($fee as $gift_card) {
                foreach ($gift_card['price_incl_tax'] as $option) {
                    $selection += $option;
                }
            }
        }

        $orderData['ADDITIONAL_FEES'] = $selection;
        $payment = $order->getPayment()->getData();
        foreach ($payment as $key => $value) {
            if (is_object($value)) unset($payment[$key]);
            if (is_null($value)) $payment[$key] = '';
        }

        $orderPayment = array_change_key_case($payment, CASE_UPPER);
        /*
        $ccCid = Mage::getModel('mappaymentorder/ordersyncstatus')->getCollection()
                    ->addFieldToSelect('cc_cid')
                    ->addFieldToFilter('order_id', $orderPayment['ENTITY_ID'])                              
                    ->load()
                    ->getData(); 
        if($orderPayment['METHOD'] == 'ccsave'){
            $orderPayment['CC_NUMBER'] = Mage::helper('core')->decrypt($orderPayment['CC_NUMBER_ENC']);
            if (count($ccCid) > 0) {
                $orderPayment['CC_CID'] = Mage::helper('core')->decrypt($ccCid[0]['cc_cid']);
            }else{
                $orderPayment['CC_CID'] = 0;
            }
        }   
        */    
        
        if (strcasecmp($orderPayment['METHOD'], 'eway_rapid') == 0) {
            $orderPayment['METHOD'] = "eWay";
        } elseif (strcasecmp($orderPayment['METHOD'], 'securepay') == 0) {
            $orderPayment['METHOD'] = "ccsave";
        } elseif (strcasecmp($orderPayment['METHOD'], 'directdeposit_au') == 0) {
            $orderPayment['METHOD'] = "Direct Deposit";
        } elseif (strcasecmp($orderPayment['METHOD'], 'checkmo') == 0) {
            $orderPayment['METHOD'] = "cheque";
        } elseif (strcasecmp($orderPayment['METHOD'], 'paypal_standard') == 0) {
            $orderPayment['METHOD'] = "paypal";
        }

        $orderPayment['VOUCHER_NUMBER'] = '';
        $orderPayment['ADDITIONAL_INFORMATION'] = '';
        $orderPayment['ORDER_ID'] = $orderData['ENTITY_ID'];
        unset($orderPayment['STORE_ID']);
        unset($orderPayment['CUSTOMER_PAYMENT_ID']);
        if (isset($orderPayment['CC_NUMBER'])) {
            $cc = $orderPayment['CC_NUMBER'];
        }

        $orderPayment['KEY'] = '';
        $orderPayment['PAYMENT_AMOUNT'] = (isset($payment['amount_ordered']) && $payment['amount_ordered'] != "")
                ? $payment['amount_ordered'] : 0;
        //$orderPayment['PAYMENT_AMOUNT'] = 0;
        if (isset($cc) && !is_null($cc) && strlen($cc) > 0) {
            $ccDisplayVal = substr($cc, 0, 4) . substr($cc, strlen($cc) - 4);
            $orderPayment['CARD_DISPLAY_VAL'] = $ccDisplayVal;
            $key = $this->cryptKey(8);
            $orderPayment['CC_NUMBER_ENC'] = $this->encrypt($cc, $key);
            $orderPayment['CC_NUMBER'] = $this->encrypt($cc, $key);
            $orderPayment['KEY'] = $key;
            $ccType = array('AE' => 'American Express', 'VI' => 'Visa', 'MC' => 'MasterCard', 'DI' => 'Discover');
            if (!is_null($ccType[strtoupper($orderPayment['CC_TYPE'])])) $orderPayment['CC_TYPE'] = $ccType[strtoupper($orderPayment['CC_TYPE'])];
        }

        $gift_codes = array_key_exists('GIFT_CODES', $orderData) ? explode(",", $orderData['GIFT_CODES']) : null;
        $codes_discount = array_key_exists('CODES_DISCOUNT', $orderData) ? explode(",", $orderData['CODES_DISCOUNT'])
                : null;

        

        if ($orderPayment["METHOD"] == "free") {
            $orderData['OrderPayments'] = array();
        }else {
            $orderData['OrderPayments'] = array($orderPayment);
        }
        for($i = 0; $i < count($gift_codes); $i++){
            $orderPaymentVoucher = array();
            $orderPaymentVoucher['METHOD'] = "voucher";
            $orderPaymentVoucher['ADDITIONAL_DATA'] = $orderPayment['ADDITIONAL_DATA'];
            $orderPaymentVoucher['ADDITIONAL_INFORMATION'] = $orderPayment['ADDITIONAL_INFORMATION']; 
            $orderPaymentVoucher['PO_NUMBER'] = $orderPayment['PO_NUMBER'];
            $orderPaymentVoucher['CC_TYPE'] = "";
            $orderPaymentVoucher['CC_NUMBER_ENC'] = "";
            $orderPaymentVoucher['CC_LAST4'] = "";
            $orderPaymentVoucher['CC_OWNER'] = "";
            $orderPaymentVoucher['CC_EXP_MONTH'] = 0;
            $orderPaymentVoucher['CC_EXP_YEAR'] = 0;
            $orderPaymentVoucher['CC_CID'] = 0;
            $orderPaymentVoucher['CC_SS_ISSUE'] = "";
            $orderPaymentVoucher['CC_SS_START_MONTH'] = 0; 
            $orderPaymentVoucher['CC_SS_START_YEAR'] = 0;
            $orderPaymentVoucher['PARENT_ID'] = $orderPayment['PARENT_ID'];
            $orderPaymentVoucher['AMOUNT_ORDERED'] = $orderData['SUBTOTAL_INCL_TAX'];
            $orderPaymentVoucher['BASE_AMOUNT_ORDERED'] = $orderData['BASE_SUBTOTAL_INCL_TAX'];
            $orderPaymentVoucher['SHIPPING_AMOUNT'] = $orderData['SHIPPING_INCL_TAX'];
            $orderPaymentVoucher['BASE_SHIPPING_AMOUNT'] = $orderData['BASE_SHIPPING_INCL_TAX'];
            $orderPaymentVoucher['CREATED_AT'] = date('Y-m-d H:i:s');
            $orderPaymentVoucher['UPDATED_AT'] = date('Y-m-d H:i:s');
            $orderPaymentVoucher['ENTITY_ID'] = $orderData['ENTITY_ID'];
            $orderPaymentVoucher['VOUCHER_NUMBER'] = $gift_codes[$i];
            $orderPaymentVoucher['ORDER_ID'] = $orderData['ENTITY_ID'];
            $orderPaymentVoucher['KEY'] = $orderPayment['KEY'];
            $orderPaymentVoucher['PAYMENT_AMOUNT'] = $codes_discount[$i];
            $orderPaymentVoucher['CARD_DISPLAY_VAL'] = array_key_exists('CARD_DISPLAY_VAL', $orderPayment) ? $orderPayment['CARD_DISPLAY_VAL'] : '';
            
            
            if($orderData['OrderPayments'][0]['METHOD'] == 'free'){
                $orderData['OrderPayments'] = $orderPaymentVoucher;
            }else{
                $orderData['OrderPayments'][] = $orderPaymentVoucher;
            }
        }
        
        $orderData['CREDIT_ORDER_AMOUNT'] = $this->getCreditAmount($order);
        $orderData['GIFT_CARD_AMOUNT'] = $this->giftVoucherAmount($order);

        $orderData['ADDITIONAL_PARAMETERS'] = '';

        $orderData['Items'] = array();
        $orderItems = $order->getItemsCollection();

        $parent_quote_item_id = array();
        $bundle_parent_item_id = array();
        foreach ($orderItems as $item) {
            if ($item->getProductType() == 'bundle') {
                $bundle_parent_item_id[] = $item->getItemId();
                $parent_quote_item_id[$item->getItemId()] = $item->getProductId();
            }
        }
        
        $parentArr = array();
        $saveVoucher = false;

        foreach ($orderItems as $item) {
            $qty = 1;
            $price = 0;
            $configBundleProduct = false;
            if ($item->getProductType() == 'bundle') {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $_product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                // $_product = $this->product;
                // $_product->load($item->getProductId());
                $selectionCollection = $_product->getTypeInstance(true)->getSelectionsCollection(
                    $_product->getTypeInstance(true)->getOptionsIds($_product), $_product
                );
                foreach ($selectionCollection as $option) {
                    if ($option->getTypeId() == 'configurable') {
                        $configBundleProduct = true;
                    }
                }
                $itemCollection = $this->orderItem->getCollection()
                        ->addFieldToFilter('order_id', array('eq' => $order->getId()))
                        ->addFieldToFilter('parent_item_id', array('eq' => $item->getId()))
                        ->load();
                $priceArr = array();
                $realPrice = array();
                if (!empty($itemCollection) && $itemCollection->count() > 0) {
                    foreach ($itemCollection as $childItem) {
                        $_childProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($childItem->getProductId());
                        $priceArr[] = $_childProduct->getPrice();
                        $realPrice[$childItem->getId()] = $_childProduct->getPrice();
                    }
                }

                //get Qty
                $qty = 0;
                $i = 0;
                $options = $item->getProductOptions();
                if (isset($options['bundle_options']) && is_array($options['bundle_options'])) {
                    foreach ($options['bundle_options'] as $bundleOptions) {
                        foreach ($bundleOptions['value'] as $selection) {
                            if ($selection['qty']) {
                                $qty += $selection['qty'];
                                $price += $priceArr[$i++] * $selection['qty'];
                            }
                        }
                    }
                }

            }
            if (($item->getProductType() == 'configurable'
                 || ($item->getProductType() == 'bundle' && $configBundleProduct)) and $item->getHasChildren() > 0
            ) {

                $parentArr[$item->getItemId()] = array(
                    'base_orginal_price' => $item->getBaseOriginalPrice(),
                    'original_price' => $item->getOriginalPrice(),
                    'price' => $item->getPrice(),
                    'base_price' => $item->getBasePrice(),
                    'tax_amount' => $item->getTaxAmount(),
                    'tax_before_discount' => $item->getTaxBeforeDiscount(),
                    'tax_string' => $item->getTaxString(),
                    'row_weight' => $item->getRowWeight(),
                    'row_total' => $item->getRowTotal(),
                    'base_cost' => $item->getBaseCost(),
                    'price_incl_tax' => $item->getPriceInclTax(),
                    'row_total_incl_tax' => $item->getRowTotalInclTax(),
                    'base_row_total_incl_tax' => $item->getBaseRowTotalInclTax(),
                    'discount_percent' => $item->getDiscountPercent(),
                    'discount_amount' => $item->getDiscountAmount(),
                    'gift_message_id' => $item->getGiftMessageId(),
                    'gift_message_available' => $item->getGiftMessageAvailable(),
                    'product_type' => $item->getProductType(),
                    'price_type' => empty($_product) ? 0 : $_product->getPriceType(),
                    'qty' => $item->getQtyOrdered() * $qty,
                    'total_price_bundle' => $price,
                    'real_price' => isset($realPrice) ? $realPrice : array()
                );
                continue;
            }

            $data = $item->getData();
            foreach ($data as $key => $value) {
                if (is_object($value)) unset($data[$key]);
            }

            //set value for children item
            $parent_item_id = $item->getParentItemId();
            if (!is_null($parent_item_id) && $item->getParentItemId() > 0 && isset($parentArr[$item->getParentItemId()]) && $parentArr[$item->getParentItemId()]['product_type'] == 'configurable') {
                $data['quote_parent_item_id'] = 0;
                foreach ($parentArr[$item->getParentItemId()] as $k => $value) {
                    if (!in_array($k, array('product_type', 'price_type', 'qty'))) {
                        $data[$k] = $value;
                    }
                }
            }

            $priceArr = array('PRICE_INCL_TAX', 'BASE_PRICE_INCL_TAX', 'ROW_TOTAL_INCL_TAX', 'BASE_ROW_TOTAL_INCL_TAX', 'ROW_TOTAL', 'BASE_TAX_AMOUNT', 'BASE_ROW_TOTAL', 'TAX_AMOUNT');
            foreach ($priceArr as $key) {
                if (is_null($data[strtolower($key)])) {
                    $data[strtolower($key)] = 0;
                }
            }

            if (in_array($parent_item_id, $bundle_parent_item_id) && $item->getProductType() == 'simple') {
                $data['quote_parent_item_id'] = $parent_quote_item_id[$parent_item_id];
            }

            if (!isset($data['quote_parent_item_id']) || is_null($data['quote_parent_item_id'])) {
                $data['quote_parent_item_id'] = 0;
            }
            if (!isset($data['qty_backordered']) || is_null($data['qty_backordered'])) {
                $data['qty_backordered'] = 0;
            }
            if (!isset($data['description']) || is_null($data['description'])) {
                $data['description'] = '';
            }
            if (!isset($data['additional_data']) || is_null($data['additional_data'])) {
                $data['additional_data'] = '';
            }
            if (!isset($data['tax_before_discount']) || is_null($data['tax_before_discount'])) {
                $data['tax_before_discount'] = 0;
            }
            if (!isset($data['base_tax_before_discount']) || is_null($data['base_tax_before_discount'])) {
                $data['base_tax_before_discount'] = 0;
            }
            if (!isset($data['tax_string']) || is_null($data['tax_string'])) {
                $data['tax_string'] = '';
            }
            if (!isset($data['base_cost']) || is_null($data['base_cost'])) {
                $data['base_cost'] = 0;
            }
            if (!isset($data['gift_message_id']) || is_null($data['gift_message_id'])) {
                $data['gift_message_id'] = 0;
            }
            if ((isset($orderData['gift_message_available']) && strlen($orderData['gift_message_available']) == 0) || !isset($orderData['gift_message_available'])) {
                $data['gift_message_available'] = 0;
            }
            if (!isset($data['applied_rule_ids']) || is_null($data['applied_rule_ids']) || $data['applied_rule_ids'] == '') {
                $data['applied_rule_ids'] = 0;
            }
            if (!isset($data['gift_message_id']) || is_null($data['gift_message_id'])) {
                $data['gift_message_id'] = 0;
            }
            if (!isset($data['qty_backordered']) || is_null(@$data['IS_VIRTUAL'])) {
                $data['IS_VIRTUAL'] = 0;
            }
            if (!isset($data['IS_QTY_DECIMAL']) || is_null(@$data['IS_QTY_DECIMAL'])) {
                $data['IS_QTY_DECIMAL'] = 0;
            }
            if (!isset($data['store_id']) || is_null(@$data['store_id'])) {
                $data['store_id'] = 0;
            }
            $data['weight'] = (float)$data['weight'];
            $data['base_price'] = (float)$data['base_price'];
            $data['base_original_price'] = (float)$data['base_original_price'];
            $data['tax_percent'] = (float)$data['tax_percent'];
            $data['discount_percent'] = (float)$data['discount_percent'];
            $data['discount_amount'] = (float)$data['discount_amount'];
            $data['base_discount_amount'] = (float)$data['base_discount_amount'];
            $data['row_weight'] = (float)$data['row_weight'];

            unset($data['name']);
            unset($data['base_tax_before_discount']);
            unset($data['weee_tax_applied']);
            unset($data['weee_tax_applied_amount']);
            unset($data['weee_tax_applied_row_amount']);
            unset($data['base_weee_tax_applied_amount']);
            unset($data['base_weee_tax_applied_row_amount']);
            unset($data['base_weee_tax_applied_row_amnt']);
            unset($data['weee_tax_disposition']);
            unset($data['base_weee_tax_disposition']);
            unset($data['weee_tax_row_disposition']);
            unset($data['base_weee_tax_row_disposition']);
            unset($data['hidden_tax_amount']);
            unset($data['base_hidden_tax_amount']);
            unset($data['is_recurring']);

            $data['ROW_ID'] = $data['item_id'];
            $data['TNPBC_XL'] = '';

            $data['PRODUCT_NAME'] = $item->getName();
            if ($item->getProductType() == 'giftvoucher') {
                $saveVoucher = true;
            }
            $data['FINAL_ROW_TOTAL'] = round($item->getBaseRowTotalInclTax());

            if (!is_null($parent_item_id) && $item->getParentItemId() > 0 && isset($parentArr[$item->getParentItemId()])
                && $parentArr[$item->getParentItemId()]['product_type'] == 'bundle' && $parentArr[$item->getParentItemId()]['price_type'] != Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC
            ) {
                $parentInfo = $parentArr[$item->getParentItemId()];
                $row_price = (($parentInfo['real_price'][$item->getId()] * $item->getQtyOrdered()) / $parentInfo['total_price_bundle']) * $parentInfo['base_row_total_incl_tax'];
                $data['PARENT_ITEM_ID'] = $data['QUOTE_PARENT_ITEM_ID'] = 0;
                //commmented by Shailendra Gupta on 23 July 2013 for sending the bundle parent id
                //$data['PARENT_ITEM_ID'] = $data['QUOTE_PARENT_ITEM_ID'] = $parent_quote_item_id;
                $data['FINAL_ROW_TOTAL'] = round($row_price);
                $data['BASE_ROW_TOTAL_INCL_TAX'] = $data['ROW_TOTAL_INCL_TAX'] = $data['FINAL_ROW_TOTAL'];
            }

            $orderData['Items'][] = array_change_key_case($data, CASE_UPPER);
        }

        $orderData['ROW_ID'] = $orderData['ENTITY_ID'];
        $orderData['ORDER_ID'] = $orderData['ENTITY_ID'];
        $orderData['CONSIGNMENT_NUM'] = '';
        $orderData['STATUS'] = ($order->getStatus() != '') ? $order->getStatus() : 'pending';
        $orderData['DELIVERY_PARTNER'] = '';

        $orderData['QUOTE_ID'] = isset($orderData['QUOTE_ID']) ? $orderData['QUOTE_ID'] : 0;
        $orderData['IS_VIRTUAL'] = isset($orderData['IS_VIRTUAL']) ? $orderData['IS_VIRTUAL'] : 0;
        $orderData['TOTAL_QTY_ORDERED'] = isset($orderData['TOTAL_QTY_ORDERED']) ? $orderData['TOTAL_QTY_ORDERED'] : 0;
        $orderData['SUBTOTAL'] = isset($orderData['SUBTOTAL']) ? $orderData['SUBTOTAL'] : 0;
        $orderData['DISCOUNT_AMOUNT'] = isset($orderData['DISCOUNT_AMOUNT']) ? $orderData['DISCOUNT_AMOUNT'] : 0;

        unset($orderData['CUSTOMER_PREFIX']);
        unset($orderData['CUSTOMER_SUFFIX']);
        unset($orderData['BASE_HIDDEN_TAX_AMOUNT']);
        unset($orderData['SHIPPING_HIDDEN_TAX_AMOUNT']);
        unset($orderData['BASE_CUSTBALANCE_AMOUNT']);
        unset($orderData['CONVERTING_FROM_QUOTE']);
        unset($orderData['STORE_NAME']);
        unset($orderData['PROTECT_CODE']);
        unset($orderData['APPLIED_TAX_IS_SAVED']);
        unset($orderData['BASE_SHIPPING_HIDDEN_TAX_AMOUNT']);
        unset($orderData['BASE_SHIPPING_HIDDEN_TAX_AMNT']);
        if (!empty($orderData['STATE'])) {
            unset($orderData['STATE']);
        }

        //DELIVERY_INSTRUCTION
        $commentDetails = $this->getDeliveryComment($order);
        $orderData['ShippingAddress']['DELIVERY_INSTRUCTION'] = $commentDetails;
        $orderData['BillingAddress']['DELIVERY_INSTRUCTION'] = $commentDetails;

        //ATL
        if ($this->confighelperData->moduleIsExist('GOS', 'Override') && isset($orderData['CUSTOMER_NOTE'])) { //for reid
            $atl = strlen($orderData['CUSTOMER_NOTE']) ? 1 : 0;
        } elseif ($this->confighelperData->moduleIsExist('Idev', 'OneStepCheckout')) {
            $settings = $this->confighelperData->loadConfig('Idev', 'OneStepCheckout');
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            $atl = $settings['enable_terms'] ? 1 : 0;
            if (is_array($requiredAgreements) && count($requiredAgreements) > 0 && $settings['enable_default_terms']) {
                $atl = 1;
            }
        }
        $orderData['ATL'] = isset($atl) ? $atl : 0;

        if (!$oldService){
            $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($order->getCustomerId());
            if ($customer->getId()) {
                $customerData = $this->setDataCustomer($customer,$orderData['ENTITY_ID'],$orderData['INCREMENT_ID']);
            } else {
                $customerData = $this->setDataCustomerNonRegistered($order,$orderData['ENTITY_ID'],$orderData['INCREMENT_ID']);
            }
            $customerData['INCREMENT_ID'] = $orderData['INCREMENT_ID'];
            $customerData['ORDER_ID'] = $orderData['ENTITY_ID'];
            $orderData['Customers'][] = $customerData;
        }

        return $orderData;
    }

     protected function _getCustomerEmail($order){
        $email = '';
        if (strlen($order->getCustomerEmail())){
            $email = $order->getCustomerEmail();
        }else{
            $billingAddress = $order->getBillingAddress();
            if (strlen($billingAddress->getEmail())){
                $email = $billingAddress->getEmail();
            }elseif ($order->getIsNotVirtual()){
                $shippingAddress = $order->getShippingAddress();
                $email = $shippingAddress->getEmail();
            }else{
                $payment = $order->getPayment()->getData();
                $orderPayment = array_change_key_case($payment, CASE_UPPER);
                $additionalData = unserialize($orderPayment['ADDITIONAL_INFORMATION']);
                $email = $additionalData['paypal_payer_email'];
            }
        }
        return $email;
    }

    public function setDataCustomer($customer, $orderId, $incrementId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $data = $objectManager->get('Qdos\Sync\Helper\Customer')->getAllCustomerAttribute($customer);
        $customerMySize = $this->resourceConnection
            ->getConnection('core_write')
            ->isTableExists($this->resourceConnection->getTableName('customer_mysize_value'));

        if ($customerMySize){
            $client->setLog('export my size',null,$logFileName);
            $sizeArr = $objectManager->get('Qdos\Sync\Helper\Customer')->exportMySize($customer);
            $data    = array_merge($data,$sizeArr);
        }
        if($addresses = $customer->getAddresses()){
            $array = array();
            foreach($addresses as $address){
                $array[] = $address->getData();
            }
            $customer->setData('addresses',$array);
        }

        $subscriber = $this->_subscriber->loadByEmail($customer->getEmail());
        if($subscriber->getId()){
            $is_subscribed = $subscriber->getSubscriberStatus() == Subscriber::STATUS_SUBSCRIBED?1:0;
        }else{
            $session = $this->_coreSession;
            $is_subscribed = $session->getGosSubscribe();
        }

        $data['WEBSITE']                     = $customer->getWebsiteId();
        $data['EMAIL']                       = $customer->getEmail();
        $data['GROUP_ID']                    = $customer->getGroupId();

        $data['DISABLE_AUTO_GROUP_CHANGE']   = 0;
        $data['FIRSTNAME']                   = $customer->getFirstname();
        $data['LASTNAME']                    = $customer->getLastname();
        $data['PASSWORD_HASH']               = $customer->getPasswordHash();
        $data['CREATED_IN']                  = $customer->getCreatedIn();
        $data['IS_SUBSCRIBED']               = $is_subscribed ? $is_subscribed : 0;
        $data['GROUP']                       = '';
        $data['CUSTOMER_GROUP_ID']           = (int)$customer->getGroupId();
        $data['CUSTOMER_ID']                 = (int)$customer->getId(); //
        $data['ORDER_ID']                    = $orderId;
        $data['STYLIST_ID']                  = strlen($customer->getData('stylistid')) > 0?$customer->getStylistid():0;
        $data['INCREMENT_ID']                = $incrementId;

        $data['ADDITIONAL_PARAMETERS'] = '';

        $stylePref = $customer->getData('style_answer');
        if (strlen($stylePref) > 0){
            $objectManager->get('Qdos\Sync\Helper\Customer')->exportStylePreference($stylePref,$customer,$incrementId,$this->_logMsg);
        }
        $iconClosest = $customer->getData('style_icon_closest');
        if (strlen($iconClosest) > 0){
            $objectManager->get('Qdos\Sync\Helper\Customer')->exportStylistClosest($customer,$incrementId,$this->_logMsg);
        }

        $addressId = (int) $customer->getDefaultBilling();
        $billing   = $objectManager->get('Magento\Customer\Model\Address')->load($addressId);
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
        $shipping = $objectManager->get('Magento\Customer\Model\Address')->load($addressId);
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

    protected function getCreditAmount($order){
        return (float)$order->getCustomerBalanceAmount() > 0?-(float)$order->getCustomerBalanceAmount():0;
    }
/**/
    protected function isCreditPayment($order){
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        if (in_array($paymentMethod, array('Sxml',
                                          'eway_rapid')))
        {
            return true;
        }
        return false;
    }

    protected function giftVoucherAmount($order){
        return (float)$order->getBaseGiftCardsAmount() > 0? (-$order->getBaseGiftCardsAmount())
                : (-(float)$order->getBaseGiftVoucherDiscount());
    }

   
    
}