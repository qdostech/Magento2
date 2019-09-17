<?php
namespace Neo\Winery\Block\Adminhtml;
class Grape extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_grape';/*block grid.php directory*/
        $this->_blockGroup = 'Neo_Winery';
        $this->_headerText = __('Grape');
        $this->_addButtonLabel = __('Import Grape Data');
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
            'id' => 'sync_grape',
            'label' => __('Sync Grape Data'),
            'onclick' => "setLocation('" . $this->_getSyncGrapeUrl() . "')"
        ];
        $this->buttonList->add('sync_categories', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getSyncGrapeUrl()
    {
        return $this->getUrl(
            'winery/syncgrape/syncgrape'
        );
    }
}
