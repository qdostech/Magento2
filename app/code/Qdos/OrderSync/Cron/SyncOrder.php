<?php
/**
 *	@author Pradeep Sanku
 */
namespace Qdos\OrderSync\Cron;
use \Psr\Log\LoggerInterface;
use \Qdos\OrderSync\Helper\Data;

class SyncOrder
{
	protected $_logger;
	protected $_dataHelper;
	public function __construct(LoggerInterface $logger,Data $dataHelper){
		$this->_logger = $logger;
		$this->_dataHelper = $dataHelper;
	}

	public function execute(){
		$this->_logger->info(__METHOD__);
		$this->_dataHelper->getProductExport();
		return $this;
	}
}