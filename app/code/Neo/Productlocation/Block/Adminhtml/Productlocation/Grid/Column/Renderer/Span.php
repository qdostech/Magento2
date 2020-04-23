<?php

namespace Neo\Productlocation\Block\Adminhtml\Productlocation\Grid\Column\Renderer;
class Span extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $options = $this->getColumn()->getOptions();
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());

            print_r($value);exit;
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
                return '<span>' . $this->escapeHtml($options[$value]) . '</span>';
            } elseif (in_array($value, $options)) {
                return '<span>' . $this->escapeHtml($value) . '<span>';
            }
        }
    }
}