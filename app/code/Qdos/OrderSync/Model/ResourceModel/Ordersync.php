<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */
namespace Qdos\OrderSync\Model\ResourceModel;

/**
 * Synccategories resource
 */
class Ordersync extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('ordersync_ordersync', 'id');
    }

  
}
