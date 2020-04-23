<?php
namespace Qdos\Syncevent\Block\Adminhtml;
class Syncevent extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_syncevent';/*block grid.php directory*/
        $this->_blockGroup = 'Qdos_Syncevent';
        $this->_headerText = __('Syncevent');
        $this->_addButtonLabel = __('Add New Entry'); 
        parent::_construct();
		
    }

     /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    // protected function _prepareLayout()
    // {

        
    //     $addButtonProps = [
    //         'id' => 'sync_event',
    //         'label' => __('Sync Events'),
    //         'onclick' => "setLocation('" . $this->_getSyncProducerUrl() . "')"
    //     ];
    //     $this->buttonList->add('sync_event', $addButtonProps);
        
    //     // return parent::_prepareLayout();
        
    // }

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




    /**
     *
     *
     * @param string $type
     * @return string
     */
    // protected function _getSyncProducerUrl()
    // {
    //     return $this->getUrl(
    //         'syncevent/syncevent/syncevent'
    //     );
    // }
}
