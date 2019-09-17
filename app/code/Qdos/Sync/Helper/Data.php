<?php
//include_once 'Zend/Loader.php';

//use Magento\Framework\App\Helper\AbstractHelper;
namespace Qdos\Sync\Helper;

class Data  extends \Magento\Framework\App\Helper\AbstractHelper{
   
   public function __construct(
          \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }
   public function execute()
    {
        echo "called helper data of qdos sync";
        die();
    }
}

   
