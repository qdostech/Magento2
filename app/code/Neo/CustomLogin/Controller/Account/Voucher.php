<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Neo\CustomLogin\Controller\Account;

class Voucher extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$this->_view->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $this->_view->getLayout()->getBlock('content')->append(
            $this->_view->getLayout()->createBlock('customer/account_voucher')
        );
		
        $this->_view->getLayout()->getBlock('head')->setPageTitle($this->__('My Account'));
        $this->_view->renderLayout(); 
	}
}