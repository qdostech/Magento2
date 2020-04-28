<?php
namespace Qdos\Sync\Helper;

class Api extends \Magento\Framework\App\Helper\AbstractHelper
{

  public function __construct(
    \Magento\Framework\App\Helper\Context $context,
    \Magento\Framework\App\Filesystem\DirectoryList $directory_list
	) {
    $this->directory_list = $directory_list;
		parent::__construct($context);
	}

  protected function _getClient(){
    $base = $this->directory_list->getPath('lib_internal');
    $lib_file = $base.'/Connection.php';
    require_once($lib_file);
    $client = Test();
    return $client;
  }

  public function getSoapQDOSObject($key = 1, $paramObj = ''){
    $st = '';
    $arr = array();
    if (!isset($paramObj['storeId'])) {
      $storeId = 1;
    }else{
      $storeId = $paramObj['storeId'];
    }
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $store_url = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    $client = $this->_getClient()->connect();
    if ($client != '') {
      try{
        switch($key) {
          default : // Web Method GetCategories
            $result = $client->GetCategories(array('store_url' => $store_url));
            $obj = $result->GetCategoriesResult;
          break;
          case 2 :  // Web Method GetCustomers
            $result = $client->GetCustomers(array('store_url' => $store_url));
            $obj = $result->GetCustomersResult;
          break;
          case 3 :  // Web Method GetProducts
            $result = $client->GetProducts(array('store_url' => $store_url));
            $obj = $result->GetProductsResult;
          break;
          case 4 :  // Web Method GetPromotionCatalogPriceRules
            $result = $client->GetPromotionCatalogPriceRules(array('store_url' => $store_url));
            $obj = $result->GetPromotionCatalogPriceRulesResult;
          break;
          case 5 :  // Web Method GetPromotionShoppingCartPriceRule
            $result = $client->GetPromotionShoppingCartPriceRule(array('store_url' => $store_url));
            $obj = $result->GetPromotionShoppingCartPriceRuleResult;
          break;
          case 6 :  // Web Method GetProductImages
            $result = $client->GetProductImages(array('store_url' => $store_url));
            $obj = $result->GetProductImagesResult;
          break;
          case 7 : // Web Method GetCustomer
            $result = $client->GetCustomersCSV(array('store_url' => $store_url,'CUSTOMER_ID'=>'','CUSTOMER_EMAIL'=>''));
            $obj = $result->GetCustomersCSVResult;
          break;
          case 8 : // Web Method GetCustomerGroup
            $result = $client->GetCustomerGroup(array('store_url' => $store_url));
            $obj = $result->GetCustomerGroupResult;
          break;
          case 9 : // Web Method GetStore
            $result = $client->GetStore(array('store_url' => $store_url));
            $obj = $result->GetStoreResult;
          break;
          case 10 : // Web Method GetStoreInventory
            $result = $client->GetStoreInventory(array('store_url' => $store_url));
            $obj = $result->GetStoreInventoryResult;
          break;
          case 11: //Accounts sync
            $result = $client->GetContactsAccountsCSV(array('store_url' => $store_url,'CUSTOMER_ID'=>'','CUSTOMER_EMAIL'=>'','CONTACT_TYPE'=>''));
            $obj = $result->GetContactsAccountsCSVResult;
          break;
        }//end of switch

        if (is_object($obj) > 0) {
          foreach($obj as $arr) {
            return $arr;
          }
        }
      }catch (Exception $e){
        echo "error--->".$e->getMessage();die;
      }
    }//end of if
  }// end of function getSoapQDOSObject

}