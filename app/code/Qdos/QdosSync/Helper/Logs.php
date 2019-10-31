<?php
/**
 * Copyright © 2019 Qdos . All rights reserved.
 */

namespace Qdos\QdosSync\Helper;
// use \Psr\Log\LoggerInterface;

class Logs extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list
    )
    {
        parent::__construct($context);
        $this->directory_list = $directory_list;
    }

    public function deleteLogs($logDays)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/deletelog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("delete log cron executed");

        $date = strtotime('-' . $logDays . ' days');
        //Mage::log('deleteLogs date '.$date, null, 'file.log', true);
        $skipLog = "order_generation_";
        $files = scandir($this->directory_list->getPath('var') . '/log/');
        foreach ($files as $file) {
            $modified_time = filemtime($this->directory_list->getPath('var') . '/log/' . $file);
            if ($modified_time < $date) {
                $pos = strpos($file, $skipLog);
                if ($pos === false && $file != '..') {
                    //Mage::log('modified_time '.$modified_time, null, 'file.log', true);
                    //Mage::log('file '.$file, null, 'file.log', true);
                    unlink($this->directory_list->getPath('var') . '/log/' . $file);
                    $logger->info($file . " deleted file");
                }
            }
        }
    }

    public function deleteOrderLogs($logDays)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/deletelog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("delete order log cron executed");

        $date = strtotime('-' . $logDays . ' days');
        //Mage::log('deleteOrderLogs date '.$date, null, 'file.log', true);
        $skipLog = "order_generation_";
        $files = scandir($this->directory_list->getPath('var') . '/log/');
        foreach ($files as $file) {
            $modified_time = filemtime($this->directory_list->getPath('var') . '/log/' . $file);
            if ($modified_time < $date) {
                $pos = strpos($file, $skipLog);
                if ($pos === true && $file != '..') {
                    //Mage::log('modified_time '.$modified_time, null, 'file.log', true);
                    //Mage::log('file '.$file, null, 'file.log', true);
                    unlink($this->directory_list->getPath('var') . '/log/' . $file);
                    $logger->info($file . " deleted file");
                }
            }
        }
    }

}