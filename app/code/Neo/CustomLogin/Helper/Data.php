<?php

namespace Neo\CustomLogin\Helper;
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
     protected $scopeConfig;

     public function __construct(        
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
            ) {
            $this->scopeConfig = $scopeConfig;
            }

    /**
     * @author DM
     * @return string
     */
    public function getForgotPasswordEmail()
    {
        return $this->_scopeConfig->getValue('customer_qdos/customer_qdos_group/forgot_pass',
                                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}