<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */
namespace Neo\Mappaymentorder\Model\ResourceModel;


class Mappaymentorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mappaymentorder', 'mappaymentorder_id');
    }

  
}
