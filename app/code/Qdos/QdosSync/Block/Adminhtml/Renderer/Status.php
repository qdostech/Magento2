<?php

namespace Qdos\QdosSync\Block\Adminhtml\Renderer;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $_objectManager->get('Qdos\Sync\Helper\Config');
        $options = $this->getColumn()->getOptions();
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());
            if (is_array($value)) {
                $res = array();
                foreach ($value as $item) {
                    if (isset($options[$item])) {
                        $res[] = $this->escapeHtml($options[$item]);
                    } elseif ($showMissingOptionValues) {
                        $res[] = $this->escapeHtml($item);
                    }
                }
                return '<span>' . implode(', ', $res) . '</span>';
            } elseif (isset($options[$value])) {
                return '<div style="text-transform: uppercase;font: bold 10px/16px Arial, Helvetica, sans-serif;color:white;font-weight:bold;background:' . $helper->getStatusColor($value) . ';border-radius:8px;width:100%">' . $this->escapeHtml($options[$value]) . '</div>';
            } elseif (in_array($value, $options)) {
                return '<div style="text-transform: uppercase;font: bold 10px/16px Arial, Helvetica, sans-serif;color:white;font-weight:bold;background:' . $helper->getStatusColor($value) . ';border-radius:8px;width:100%">' . $this->escapeHtml($value) . '</div>';
            }
        }
    }
}