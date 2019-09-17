<?php
/**
*	@author Pradeep Sanku
*/
namespace Qdos\QdosSync\Cron;
use \Psr\Log\LoggerInterface;
// use \Qdos\QdosSync\Helper\Data;

class SynchCategory
{
	protected $_logger;
	// protected $_qdosSyncHelperData;

	public function __construct(LoggerInterface $logger){
		$this->_logger = $logger;
		//$this->_qdosSyncHelperData = $qdosSyncHelperData;
	}

	public function execute(){
		// $this->_qdosSyncHelperData->test();
		// $this->_logger->info('Hello Pradeep!, Your Cron Works Properly !');
		$this->_logger->info(__METHOD__	);
		//$this->_logger->debug('Hello Pradeep!, Your Cron Works Properly !');
		return $this;
	}
}