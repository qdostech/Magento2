<?php

namespace Qdos\QdosSync\Block\Adminhtml\Sync\Renderer;

class Syncdatetime extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row){
        $value =  $row->getData('last_sync');
        return $formattedDate = date('d/m/Y h:i:s A', strtotime($value));
        //return '<span>'.$value.'</span>';
    }
}
