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
        $_product = $observer->getProduct();  // you will get product object
        $sku = $_product->getSku(); // for sku
        $name = $_product->getName();
        $url = preg_replace('#[^0-9a-z]+#i', '-', $name);
        $url = rtrim($url, "-"); 
	    $urlKey = strtolower($url);
	    $storeId = (int) $this->_storeManager->getStore()->getStoreId();

	    $isUnique = $this->checkUrlKeyDuplicates($sku, $urlKey, $storeId);
	    if ($isUnique) {
	    	//$_product->setUrlKey($urlKey);
	        return $urlKey;
	    } else {
	    	$randomNo = mt_rand(100000, 999999);
            $newUrlKey = $urlKey . '-' . $randomNo;
	    	$_product->setUrlKey($newUrlKey);
	        return $newUrlKey;
	    }

    }

    /*
	 * Function to check URL Key Duplicates in Database
	 */

	public function checkUrlKeyDuplicates($sku, $urlKey, $storeId)
	{
	    $urlKey .= '.html';

	    $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

	    $tablename = $connection->getTableName('url_rewrite');
	    $sql = $connection->select()->from(
	                    ['url_rewrite' => $connection->getTableName('url_rewrite')], ['request_path', 'store_id']
	            )->joinLeft(
	                    ['cpe' => $connection->getTableName('catalog_product_entity')], "cpe.entity_id = url_rewrite.entity_id"
	            )->where('request_path IN (?)', $urlKey)
	            //->where('store_id IN (?)', $storeId)
	            ->where('cpe.sku not in (?)', $sku);

	    $urlKeyDuplicates = $connection->fetchAssoc($sql);

	    if (!empty($urlKeyDuplicates)) {
	        return false;
	    } else {
	        return true;
	    }
	}
}