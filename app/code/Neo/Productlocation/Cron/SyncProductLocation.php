<?php
/**
 *	@author Rahul
 */
namespace Neo\Productlocation\Cron;
use \Psr\Log\LoggerInterface;
use \Neo\Productlocation\Helper\Getlocation;

class SyncProductLocation
{
	protected $_logger;
	protected $_dataHelper;
	public function __construct(LoggerInterface $logger,Getlocation $dataHelper){
		$this->_logger = $logger;
		$this->_dataHelper = $dataHelper;
	}

	public function execute(){
		$this->_logger->info(__METHOD__);
		$this->_dataHelper->syncGetLocation();
		return $this;
	}
}