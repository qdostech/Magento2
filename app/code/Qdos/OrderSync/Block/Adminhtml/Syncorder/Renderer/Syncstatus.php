<?php
 
namespace Qdos\OrderSync\Block\Adminhtml\Syncorder\Renderer;
 
use Magento\Framework\DataObject;
 
class Syncstatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
   
    public function render(DataObject $row)
    {
        $rowData = $row->getData();
        $orderId = $rowData['entity_id'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('order_sync_status'); //gives table name with prefix
         
        //Select Data from table
        $sql = "Select sync_status FROM " . $tableName . " where order_id = ".$orderId;
        $result = $connection->fetchAll($sql);
        if(count($result) > 0){
            $status = $result[0]['sync_status'];
        }else{
            $status = '';
        }
        return $status;       
    }
}