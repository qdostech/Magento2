<?php
 
namespace Neo\Storemapping\Block\Adminhtml\Storemapping\Renderer;
 
use Magento\Framework\DataObject;
 
class Syncpermissions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
   
    public function render(DataObject $row)
    {
        $rowData = $row->getData();
        $sync_type = $rowData['sync_type'];

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();    
        $arrSyncs = $objectManager->get("\Neo\Storemapping\Model\Storemapping")->getSyncsList();

	    $arrRow = explode(',', $sync_type);
	    $allowedSyncs = array();
	    foreach ($arrRow as $key => $syncId) {
	      $allowedSyncs[] = $arrSyncs[$syncId];
	    }

	    return implode(' , ', $allowedSyncs);

    }
}