<?php

class TestApp extends \Magento\Framework\App\Http
{
	public function launch()
	{
        
        if (isset($_GET['val']))
        {
            $value = $_GET['val'];             
            
            if ("product" == $value)
            {
                $this->deleteProducts();
            }

            if ("category" == $value)
            {
                $this->deleteCats();
            }

            if ("attribute" == $value)
            {
                $this->deleteAttributes();
            }
            else{
                echo "wrong input";
                exit;
            }
        }
        else{
            echo "Please specify delete value";
            exit;
        }
    }


    public function deleteProducts()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/scripts.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("sd");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $collection = $productCollection->create()->addAttributeToSelect('*')->load();
        $app_state = $objectManager->get('\Magento\Framework\App\State');
        $app_state->setAreaCode('frontend');

        foreach ($collection as $product){
            try {
                echo 'Deleted '.$product->getName().PHP_EOL;
                $product->delete();

            } catch (Exception $e) {
                echo 'Failed to remove product '.$product->getName() .PHP_EOL;
                echo $e->getMessage() . "\n" .PHP_EOL;
            }   
        }
    }


    function deleteAllCategories($objectManager) 
    {
        $categoryFactory = $objectManager->get('Magento\Catalog\Model\CategoryFactory');
        $newCategory = $categoryFactory->create();
        $collection = $newCategory->getCollection();
        $objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);

        foreach($collection as $category) {

            $category_id = $category->getId();

            if( $category_id <= 2 ) continue;

            try {
                $category->delete();
                echo 'Category Removed '.$category_id .PHP_EOL;
            } catch (Exception $e) {
                echo 'Failed to remove category '.$category_id .PHP_EOL;
                echo $e->getMessage() . "\n" .PHP_EOL;
            }
        }
    }
}
 