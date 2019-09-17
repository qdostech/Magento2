<?php

namespace Qdos\QdosSync\Block\Adminhtml\Renderer;

class Log extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = $this->getColumn()->getActions();

        if ($row->getLogId()) {
            $last_sync = $row->getData('log_id');
        } else {
            $last_sync = $row->getData('last_log_id');
        }
        if (empty($actions) || !is_array($actions) || empty($last_sync)) {
            return '&nbsp;';
        }
        if (sizeof($actions) == 1 && !$this->getColumn()->getNoLink()) {
            foreach ($actions as $action) {
                if (is_array($action)) {
                    return $this->_toScriptHtml($action, $row);
                }
            }
        }
        return parent::render($row);
    }

    /**
     * @param $action
     * @param $row
     * @return string
     */
    protected function _toScriptHtml($action, $row)
    {
        $id = $row->getData('last_log_id');
        return '<a onclick="viewLog(' . $id . ')">View log</a>';
    }
}