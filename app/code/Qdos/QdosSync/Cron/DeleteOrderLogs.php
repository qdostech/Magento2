<?php
/**
 *	@author Pradeep Sanku
 */
namespace Qdos\QdosSync\Cron;
use \Psr\Log\LoggerInterface;

class DeleteOrderLogs
{
    protected $_logger;
    public function __construct(LoggerInterface $logger){
        $this->_logger = $logger;
    }

    public function execute(){
        $this->_logger->debug('Cron Works in Delete Order Logs');
        return $this;
    }
}