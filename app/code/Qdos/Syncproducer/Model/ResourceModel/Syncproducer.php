<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */
namespace Qdos\Syncproducer\Model\ResourceModel;

/**
 * Syncproducer resource
 */
class Syncproducer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('syncproducer_syncproducer', 'id');
    }

  
}
