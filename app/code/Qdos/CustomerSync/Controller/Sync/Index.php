<?php
/**
 *
 * Copyright © 2015 Qdoscommerce. All rights reserved.
 */
namespace Qdos\CustomerSync\Controller\Sync;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
       \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        echo 'sanku'; exit;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cData = $objectManager->get('Qdos\CustomerSync\Helper\Data')->syncCustomers();
        echo "end cdata----------";die;
    }
}