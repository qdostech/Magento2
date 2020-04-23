<?php
namespace Qdos\Syncevent\Block\Adminhtml;
class Allevents extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_allevents';
        $this->_blockGroup = 'Qdos_Syncevent';
        $this->_headerText = __('Sync Event');
       // $this->_addButtonLabel = __('Add Productlocationqty'); 
        parent::_construct();
		
    }   

     /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {

        
        $addButtonProps = [
            'id' => 'sync_event',
            'label' => __('Sync Events'),
            'onclick' => "setLocation('" . $this->getUrl('syncevent/syncevent/syncevent') . "')"
        ];
        $this->buttonList->add('sync_event', $addButtonProps);


         $addButtonPropsnew = [
            'id' => 'sync_eventslogs',
            'label' => __('All Events'),
            'onclick' => "setLocation('" . $this->getUrl('syncevent/allsyncevent/index') . "')"
        ];
        $this->buttonList->add('sync_eventslogs', $addButtonPropsnew);
       $this->removeButton('add');
        return parent::_prepareLayout();
    }

}
