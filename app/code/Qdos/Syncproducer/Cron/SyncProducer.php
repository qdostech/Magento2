<?php
/**
 *	@author Pradeep Sanku
 */
namespace Qdos\Syncproducer\Cron;
use \Psr\Log\LoggerInterface;
use \Qdos\Syncproducer\Helper\Data;

class SyncProducer
{
	protected $_logger;
	protected $_dataHelper;
	public function __construct(LoggerInterface $logger,Data $dataHelper){
		$this->_logger = $logger;
		$this->_dataHelper = $dataHelper;
	}

	public function execute(){
		$this->_logger->info(__METHOD__);
		$this->_dataHelper->importProducer();
		return $this;
	}
}