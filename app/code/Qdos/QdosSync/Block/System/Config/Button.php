<?php
namespace Qdos\QdosSync\Block\System\Config;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
     protected $_template = 'Qdos_QdosSync::system/config/button.phtml';

     protected $date;

 
     public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->date = $date;
        parent::__construct($context, $data);
    }
 
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $cronstatus = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            //echo $cronstatus; die('dasgasd');
        if($cronstatus == 'not running'){
            return '';    
        }else{
            $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
            return parent::render($element);
        }
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
            return $this->_tohtml();
    }

    public function getAjaxUrl()
    {
        return $this->getUrl('qdossync/system/button');
    }

    public function getCurrentDateTime()
    {
        return $this->date->gmtDate();
    }

    public function getButtonHtml()
    {

        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'btnid',
                'label' => __('Change Cron Status'),
            ]
        );
 
        return $button->toHtml();
    }
}
