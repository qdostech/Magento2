<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */
namespace Qdos\QdosSync\Model\ResourceModel;

/**
 * Storemapping resource
 */
class Storemapping extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('qdossync_storemapping', 'id');
    }

  
}
