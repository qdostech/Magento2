<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace GOS\WholesalePayment\Model;

class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'wholesale';

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $allow_customer_group = $this->getConfigData('customer_group');
        $allowArr = explode(',',$allow_customer_group);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
		$login = $customerSession->isLoggedIn();
        $groupId = $login ? $customerSession->getCustomerGroupId() : 0;

        return parent::isAvailable($quote) && !empty($quote)
            && in_array($groupId,$allowArr);
    }
}