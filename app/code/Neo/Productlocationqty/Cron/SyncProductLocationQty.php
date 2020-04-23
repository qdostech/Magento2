<?php
/**
 *	@author Rahul
 */
namespace Neo\Productlocationqty\Cron;
use \Psr\Log\LoggerInterface;
use \Neo\Productlocationqty\Helper\Getlocationqty;

class SyncProductLocationQty
{
	protected $_logger;
	protected $_dataHelper;
	public function __construct(LoggerInterface $logger,Getlocationqty $dataHelper){
		$this->_logger = $logger;
		$this->_dataHelper = $dataHelper;
	}

	public function execute(){
		$this->_logger->info(__METHOD__);
		$this->_dataHelper->syncGetLocationQty();
		return $this;
	}
}