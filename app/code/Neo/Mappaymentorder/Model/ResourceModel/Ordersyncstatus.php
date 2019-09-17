<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */
namespace Neo\Mappaymentorder\Model\ResourceModel;

class Ordersyncstatus extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('order_sync_status', 'order_sync_status_id');
    }

  
}
