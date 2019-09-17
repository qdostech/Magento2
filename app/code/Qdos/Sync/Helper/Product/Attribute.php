<?php
namespace Qdos\Sync\Helper\Product;

set_time_limit(0);
ini_set('max_execution_time', 30000);
ini_set('memory_limit', '2048M');
ini_set('default_socket_timeout', 2000);

class Attribute extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_attributes = array();
    protected $_product;
    protected $_delimiter = ',';
   
	public function __construct(
    \Magento\Framework\App\Helper\Context $context,
    \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
    \Magento\Eav\Model\Config $eavConfig
	) {
        $this->eavConfig = $eavConfig;
        $this->directory_list = $directory_list;
		parent::__construct($context);
        
	}
    
    public function convertValueToLabel($attributeCode = '',$entityType = 'customer',$attributeValue = ''){
        $attribute = $this->eavConfig->getAttribute($entityType,$attributeCode);
        $value = '';
        if ($attribute){
            if ($attribute->usesSource()) {
                $optionsValue = array();
                foreach ($attribute->getSource()->getAllOptions(false) as $option){
                     $optionsValue[$option['value']] = trim($option['label']);
                }
                $frontendInput = $attribute->getFrontendInput();
                $result = array();
                switch ($frontendInput) {
                    case 'multiselect':
                    case 'checkbox':
                        $attributeValue = explode(',',$attributeValue);
                        break;
                    case 'select':
                    case 'boolean':
                        $attributeValue = (array)$attributeValue;
                        break;
                    default:
                        break;
                }
                foreach ($attributeValue as $val) {
                    if (array_key_exists(trim($val), $optionsValue)) {
                        $result[] = $optionsValue[$val];
                    }
                }
                $value = implode(',', $result);
            }else{
                $value = $attributeValue;
            }
        }
        return $value;
    }
    
}