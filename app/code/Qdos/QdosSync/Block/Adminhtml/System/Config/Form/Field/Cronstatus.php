<?php
namespace Qdos\QdosSync\Block\Adminhtml\System\Config\Form\Field;
//use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Cronstatus extends \Magento\Framework\Data\Form\Element\Label
{
    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        return $html ;
    }
}

