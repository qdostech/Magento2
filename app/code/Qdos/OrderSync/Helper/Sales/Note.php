<?php

namespace Qdos\OrderSync\Helper\Sales;

class Note extends \Magento\Framework\App\Helper\AbstractHelper
{
   
	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Qdos\Sync\Helper\Config $confighelperData,
        \Qdos\Sync\Helper\Product\Attribute $syncAttributes
	) {

        parent::__construct($context);
	    $this->directory_list = $directory_list;
        $this->confighelperData = $confighelperData;
        $this->syncAttributes = $syncAttributes;
	}

    public function exportNote($order,$storeId = 1,$logMsg = array()){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logFileName = "order_generation_".date('Ymd').'.log';
        $result = true;
        try{
            $billing = $order->getBillingAddress();
            $shipping = $order->getShippingAddress();

            $billing_name = is_object($billing)?implode(' ',array($billing->getFirstname(),$billing->getLastname())):'';
            $shipping_name = is_object($shipping)?implode(' ',array($shipping->getFirstname(),$shipping->getLastname())):'';

            $data = array();
            $fields = array('coupon_code',
                            'free_gift_code',
                            'voucher_code',
                            'skin_concert',
                            'gift_message',
                            'gift_card_selection',
                            'delivery_comment',
                            'additional_fee',
                            'delivery_date',
                            'quoted_delivery',
                            'contact_bill_name',
                            'contact_ship_name');
            $fee = unserialize($order->getDetailsMultifees());
            $fee = is_array($fee) ? $fee : array();
            foreach ($fields as $field){
                if($field == 'contact_bill_name' || $field == 'contact_ship_name'){
                    if($billing_name == $shipping_name){
                        break;
                    }
                }
                $value = '';
                $value2 = '';
                switch ($field){
                    case 'coupon_code':
                        $couponCode = $order->getCouponCode();
                        $oCoupon = $objectManager->create('Magento\SalesRule\Model\Coupon')->load($couponCode, 'code');
                        $oRule = $objectManager->create('Magento\SalesRule\Model\Rule')->load($oCoupon->getRuleId());
                        $value = $couponCode;
                        if($oRule->getData('discount_amount')){
                            $value2 = $oRule->getData('discount_amount').'|'.$couponCode;
                        }
                        break;
                    case 'free_gift_code':
                        $gift = array();
                        $orderItems = $order->getItemsCollection();
                        foreach ($orderItems as $item){
                            $optionsArr = $item->getProductOptions();
                            $info = $optionsArr['info_buyRequest'];
                            if (isset($info['freegift_with_code']) && !in_array(trim($info['freegift_with_code']),$gift)){
                                $gift[] = trim($info['freegift_coupon_code']);
                            }
                        }
                        if (is_array($gift) and count($gift) > 0) {
                            $value = implode(',',$gift);
                        }
                        break;
                    case 'voucher_code':
                        $gifts = unserialize($order->getGiftCards());
                        $code = array();
                        if($gifts){
                            foreach ($gifts as $gift){
                                $code[] = $gift['c'];
                            }
                        }
                        if (is_array($code) and count($code) > 0){
                            $value = implode(',',$code);
                        }
                        break;
                    case 'skin_concert':
                        $order_value = $order->getCustomerSkintype();
                        $value = $this->syncAttributes->convertValueToLabel('skintype','customer',$order_value);
                        break;
                    case 'gift_message':
                        $message = array();
                        foreach ($fee as $gift_card) {
                            $message[] = $gift_card['message'];
                        }
                        if (is_array($message) and count($message) > 0) {
                            $value = implode(',', $message);
                        }
                        break;
                    case 'gift_card_selection':
                        $selection = array();

                        foreach ($fee as $gift_card){
                            foreach($gift_card['options'] as $option){
                                $selection[] = $option;
                            }
                        }
                        if (is_array($selection) and count($selection) > 0){
                            $value = implode(',',$selection);
                        }

                        break;
                    case 'additional_fee':
                        $selection = 0;
                        foreach ($fee as $gift_card){
                            foreach($gift_card['price_incl_tax'] as $option){
                                $selection += $option;
                            }
                        }
                        if ($selection > 0){
                            $value = $selection;
                        }
                        break;
                    case 'delivery_comment':
                        $value = $order->getSafedropComment();
                        break;
                    case 'delivery_date':
                        $configHelper = $this->confighelperData;
                        if ($configHelper->moduleIsExist('MDN','SalesOrderPlanning') && in_array($order->getStatus(),array('complete','close'))){
                            $planning = Mage::getModel('SalesOrderPlanning/Planning')->load($order->getId() , 'psop_order_id');
                            if ($planning){
                                $value = Mage::helper('core')->formatDate($planning->getDeliveryDate(), 'short');
                            }
                        }
                        break;
                    case 'quoted_delivery':
                        $value = $order->getQuotedDelivery();
                        break;
                    case 'contact_bill_name':
                        $value = $billing_name;
                        break;
                    case 'contact_ship_name':
                        $value = $shipping_name;
                        break;
                }

                if (strlen($value) > 0) {
                    $data[] = array('INCREMENT_ID' => $order->getIncrementId(),
                                    'TYPE' => $field,
                                    'NOTE' => $value);
                }
                
                if(strlen($value2) > 0){
                    $data[] = array('INCREMENT_ID' => $order->getIncrementId(),
                                    'TYPE' => 'coupon_amount',
                                    'NOTE' => $value2);
                    $value2 = '';
                }
            }

            $base = $this->directory_list->getPath('lib_internal');
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $lib_file = $base.'/Connection.php'; 
            require_once($lib_file);
            $client = Test();

            $resultClient = $client->connect();

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path');
            $resultClient = $this->confighelperData->run($resultClient, 'SaveOrderNoteCSV', array('store_url' => $store_url, 'details' => $data));
            if ($resultClient->outErrorMsg && strlen($resultClient->outErrorMsg) > 0) {
                $result = false;
                $logMsg[] = $resultClient->outErrorMsg;
            } else {
                $logMsg[] = 'SaveOrderNoteCSV: Exported ' . count($data) . ' record(s)';
            }
        }catch(Exception $e){
            $result = false;
            $logMsg[] = $this->addError($e->getMessage());
        }

        return $result;
    }
    
}