<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */
namespace Qdos\QdosSync\Model\ResourceModel;

/**
 * Sync resource
 */
class Log extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('qdos_activity_log', 'log_id');
    }

  
}
