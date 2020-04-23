<?php

namespace Neo\Productlocation\Block\Adminhtml\Productlocation\Grid\Column\Renderer;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Render action
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $status = $row->getData('status');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Qdos\Sync\Helper\Config');
        $statusLabel = $objectManager->get('\Qdos\QdosSync\Model\Activity')->getActivityTypeByKey($status);
        //return $statusLabel;
        return '<div style="text-transform: uppercase;font: bold 10px/16px Arial, Helvetica, sans-serif;color:white;font-weight:bold;background:' . $helper->getStatusColor($status) . ';border-radius:8px;width:100%;text-align: center">' . $this->escapeHtml($statusLabel) . '</div>';
    }
}