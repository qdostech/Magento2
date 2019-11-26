<?php
/**
 * Copyright © 2019 Qdos . All rights reserved.
 */

namespace Qdos\QdosSync\Helper;

use \Psr\Log\LoggerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;

class Logs extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SENDER_NAME = 'Customer Support';
    const SENDER_EMAIL = 'support@qdos.com.au';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        \Magento\Framework\App\State $state
    )
    {
        parent::__construct($context);
        $this->logger = $logger;
        $this->directory_list = $directory_list;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_resourceConfig = $resourceConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->state = $state;
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


    public function sendMailForSyncFailed($syncType)
    {
        try {
            $this->inlineTranslation->suspend();
            // Transactional Email Template's ID        
            $templateId = $this->_scopeConfig->getValue('qdosConfig/cron_status/sync_fail_template_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $sender = [
                'name' => $this->escaper->escapeHtml(self::SENDER_NAME),
                'email' => $this->escaper->escapeHtml(self::SENDER_EMAIL),
            ];

            $cronStatusCheckInterval = $this->_scopeConfig->getValue('qdosConfig/cron_status/check_intervel_in_hours', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            // Set variables that can be used in email template
            $vars = array('hours' => $cronStatusCheckInterval, 'synctype' => $syncType);
            // Set recepient information
            $recepientName = 'Support';
            $recepientEmail = $this->_scopeConfig->getValue('qdosConfig/cron_status/sync_fail_email_to', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    'templateVar' => $vars,
                ])
                ->setFrom($sender)
                ->addTo($recepientEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }


    public function sendProcessFailureMail($processName)
    {
        try {
            $this->inlineTranslation->suspend();
            // Transactional Email Template's ID        
            $templateId = 'cron_status_email';
            $email_to = $this->_scopeConfig->getValue('qdosConfig/cron_status/cron_status_email_to', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $sender = [
                'name' => $this->escaper->escapeHtml(self::SENDER_NAME),
                'email' => $this->escaper->escapeHtml(self::SENDER_EMAIL),
            ];

            $currentDateTime = date('Y-m-d H:i:s');

            $cronStatusCheckInterval = $this->_scopeConfig->getValue('qdosConfig/cron_status/check_intervel_in_hours', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            // Set variables that can be used in email template
            $email_template_variables = array(
                'site_name' => $this->_storeManager->getStore()->getBaseUrl(),
                'cron_process' => $processName,
                'cron_schedule' => $currentDateTime
                // Other variables for our email template.
            );

            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    'templateVar' => $email_template_variables,
                ])
                ->setFrom($sender)
                ->addTo($email_to)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }


    public function sendMailForCronStatus()
    {
        $cronStatusUpdateTime = $this->_scopeConfig->getValue('qdosConfig/cron_status/current_cron_updated_time', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $cronStatusCheckInterval = $this->_scopeConfig->getValue('qdosConfig/cron_status/check_intervel_in_hours', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //$cronStatusResetInterval = Mage::getStoreConfig('qdos_sync_config/current_cron_status/reset_cron_status_automatically');

        $currentDateTime = date('Y-m-d H:i:s');
        $timeDiff = strtotime($currentDateTime) - strtotime($cronStatusUpdateTime);

        $this->inlineTranslation->suspend();

        if ($timeDiff > ($cronStatusCheckInterval * 3600)) {

            // Transactional Email Template's ID
            $templateId = $this->_scopeConfig->getValue('qdosConfig/cron_status/cron_template_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            // Set sender information          
            //$senderName = Mage::getStoreConfig('trans_email/ident_support/name');
            $senderName = self::SENDER_NAME;
            $senderEmail = self::SENDER_EMAIL;

            $sender = array('name' => $senderName,
                'email' => $senderEmail);

            // Set recepient information
            $recepientName = 'Support';
            $recepientEmail = $this->_scopeConfig->getValue('qdosConfig/cron_status/cron_status_email_to', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            // Get Store ID    
            $storeId = $this->_storeManager->getStore()->getId();

            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

            // Set variables that can be used in email template
            $vars = array('hours' => $cronStatusCheckInterval);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    'templateVar' => $vars,
                ])
                ->setFrom($sender)
                ->addTo($recepientEmail)
                ->getTransport();
            $transport->sendMessage();

            $this->inlineTranslation->resume();
        }

        if ($timeDiff > ($cronStatusResetInterval * 3600) && $cronStatusResetInterval != '') {

            // Transactional Email Template's ID
            $templateId = $this->_scopeConfig->getValue('qdosConfig/cron_status/cron_template_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $this->_resourceConfig->saveConfig('qdosConfig/cron_status/current_cron_status', "Not Running", 'default', 0);
            // Set sender information          
            //$senderName = Mage::getStoreConfig('trans_email/ident_support/name');
            $senderName = self::SENDER_NAME;
            $senderEmail = self::SENDER_EMAIL;

            $sender = array('name' => $senderName,
                'email' => $senderEmail);

            // Set recepient information
            $recepientName = 'Support';
            $recepientEmail = $this->_scopeConfig->getValue('qdosConfig/cron_status/cron_status_email_to', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            // Get Store ID    
            $storeId = $this->_storeManager->getStore()->getId();

            // Set variables that can be used in email template
            $vars = array('hours' => $cronStatusCheckInterval);
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    'templateVar' => $vars,
                ])
                ->setFrom($sender)
                ->addTo($recepientEmail)
                ->getTransport();
            $transport->sendMessage();

            $this->inlineTranslation->resume();
        }
    }
}