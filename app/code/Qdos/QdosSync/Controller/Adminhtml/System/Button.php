<?php
namespace Qdos\QdosSync\Controller\Adminhtml\System;
 
use \Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\Config\ScopeConfigInterface;

 
class Button extends \Magento\Backend\App\Action
{
    protected $_logger;

    protected $configWriter;

    protected $date;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->_logger = $logger;
        $this->configWriter = $configWriter;
        $this->date = $date;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->_logger->debug('changing status..');

        //$status = $this->getRequest()->getParams();
        //if($status['status'] == 'notrunning')
        $status = 'not running';
        $date = $this->date->gmtDate();

        $this->configWriter->save('qdosConfig/cron_status/current_cron_status',  $status, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);

        $this->configWriter->save('qdosConfig/cron_status/current_cron_updated_time',  $date, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);

        //$this->_logger->debug('changed status..');
    }  
}