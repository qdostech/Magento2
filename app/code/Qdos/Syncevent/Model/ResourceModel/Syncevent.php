<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */
namespace Qdos\Syncevent\Model\ResourceModel;

/**
 * Syncevent resource
 */
class Syncevent extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('syncevent_syncevent', 'id');
    }

  
}
