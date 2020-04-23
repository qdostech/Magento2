<?php
/**
 *	@author Rahul
 */
namespace Neo\ImageCleaner\Cron;
use \Psr\Log\LoggerInterface;

class ProductImageDelete
{
	protected $_logger;
	protected $_dataHelper;
	public function __construct(LoggerInterface $logger,\Neo\ImageCleaner\Helper\Data $dataHelper){
		$this->_logger = $logger;
		$this->_dataHelper = $dataHelper;
	}

	public function execute(){
		$this->_logger->info(__METHOD__);
		$this->_dataHelper->productImagesDelete();
		return $this;
	}
}