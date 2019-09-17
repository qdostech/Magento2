<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */
namespace Qdos\QdosSync\Model\ResourceModel;

/**
 * Sync resource
 */
class Sync extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('qdossync_sync', 'id');
    }

  
}
