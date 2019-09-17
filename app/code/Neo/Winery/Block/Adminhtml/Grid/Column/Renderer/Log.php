<?php

namespace Neo\Winery\Block\Adminhtml\Grid\Column\Renderer;

class Log extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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

    public function render(\Magento\Framework\DataObject $row){
        $description = $row->getData('description');
        $id = $row->getData('log_id');
        return '<a onclick="viewLog('.$id.')">View log</a>';
    }
}