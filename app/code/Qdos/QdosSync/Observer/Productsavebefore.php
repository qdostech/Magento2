<?php

namespace Qdos\QdosSync\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsavebefore implements ObserverInterface
{
	
	public function __construct(
	    \Magento\Store\Model\StoreManagerInterface $storeManager, 
	    \Magento\Framework\App\ResourceConnection $resource
	) {
	    $this->_storeManager = $storeManager;
	    $this->_resource = $resource;
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron-observer.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $_product = $observer->getProduct();  // you will get product object
        $sku = $_product->getSku(); // for sku
        $name = $_product->getName();
        //$logger->info($name);
        $url = preg_replace('#[^0-9a-z]+#i', '-', $name);
        $url = rtrim($url, "-"); 
	    $urlKey = strtolower($url);
	   
	    $storeId = (int) $this->_storeManager->getStore()->getStoreId();

	    $isUnique = $this->checkUrlKeyDuplicates($sku, $urlKey, $storeId);
	    //$logger->info($isUnique);
	    if ($isUnique) {
	    	//$_product->setUrlKey($urlKey);
	        return $urlKey;
	    } else {
	    	$randomNo = mt_rand(100000, 999999);
            $newUrlKey = $urlKey . '-' . $randomNo;
	    	$_product->setUrlKey($newUrlKey);
	    	 $logger->info("SKU--".$sku."---already exist url key--".$urlKey."--- new url key replacing---".$newUrlKey );
	        return $newUrlKey;
	    }

    }

    /*
	 * Function to check URL Key Duplicates in Database
	 */

	public function checkUrlKeyDuplicates($sku, $urlKey, $storeId)
	{

		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron-observer.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("checkUrlKeyDuplicates");

	    $urlKey .= '.html';

	    $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

	    $tablename = $this->_resource->getTableName('url_rewrite');
	    $sql = $connection->select()->from(
	                    ['url_rewrite' => $tablename], ['request_path', 'store_id']
	            )->joinLeft(
	                    ['cpe' => $this->_resource->getTableName('catalog_product_entity')], "cpe.entity_id = url_rewrite.entity_id"
	            )->where('request_path IN (?)', $urlKey)
	            //->where('store_id IN (?)', $storeId)
	            ->where('cpe.sku not in (?)', $sku);

	     $cat_sql = $connection->select()->from(
	                    ['url_rewrite' => $tablename], ['request_path', 'store_id']
	            )->joinLeft(
	                    ['cce' => $this->_resource->getTableName('catalog_category_entity')], "cce.entity_id = url_rewrite.entity_id"
	            )->where('request_path LIKE (?)', '%'.$urlKey)
	            //->where('store_id IN (?)', $storeId)
	            ->where('url_rewrite.entity_type = (?)', 'category');

	  $logger->info($sql->__toString());

	    // $logger->info($cat_sql->__toString());
	    
	    $urlKeyDuplicates = $connection->fetchAssoc($sql);
	    $cat_urlKeyDuplicates = $connection->fetchAssoc($cat_sql);

	    if (!empty($urlKeyDuplicates)) {
	        return false;
	    } else {
	    	if(!empty($cat_urlKeyDuplicates))
	    	{
	    		 return false;
	    	}else
	    	{
	       		 return true;
	  	    }
	    }
	}
}