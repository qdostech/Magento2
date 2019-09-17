<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Neo\Storemapping\Model\ResourceModel\Storemapping;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Neo\Storemapping\Model\Storemapping', 'Neo\Storemapping\Model\ResourceModel\Storemapping');
    }
}
