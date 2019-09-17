<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Neo\Mappaymentorder\Model\ResourceModel\Mappaymentorder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Neo\Mappaymentorder\Model\Mappaymentorder', 'Neo\Mappaymentorder\Model\ResourceModel\Mappaymentorder');
    }
}
