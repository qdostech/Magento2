<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Neo\Productlocation\Model\ResourceModel\Productlocation;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Neo\Productlocation\Model\Productlocation', 'Neo\Productlocation\Model\ResourceModel\Productlocation');
    }
}
