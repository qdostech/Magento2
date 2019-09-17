<?php
 
namespace Neo\Storemapping\Block\Adminhtml\Storemapping\Renderer;
 
use Magento\Framework\DataObject;
 
class Storename extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
   
    public function render(DataObject $row)
    {
        $rowData = $row->getData();
        $storeId = $rowData['store_id'];
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();    
        $store = $objectManager->create("\Magento\Store\Model\Store")->load($storeId);

        return $store->getName();          
    }
}