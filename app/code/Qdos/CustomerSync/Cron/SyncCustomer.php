<?php
/**
 *	@author Pradeep Sanku
 */
namespace Qdos\CustomerSync\Cron;
use \Psr\Log\LoggerInterface;
use Qdos\CustomerSync\Helper\Data;

class SyncCustomer
{
	protected $_logger;
	protected $_dataHelper;
	public function __construct(LoggerInterface $logger,Data $dataHelper){
		$this->_logger = $logger;
		$this->_dataHelper = $dataHelper;
	}

	public function execute(){
		$this->_logger->info(__METHOD__);
		$this->_dataHelper->syncCustomers();
		return $this;
	}
}