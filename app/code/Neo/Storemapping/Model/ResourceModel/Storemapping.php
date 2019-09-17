<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */
namespace Neo\Storemapping\Model\ResourceModel;


class Storemapping extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('storemapping', 'storemapping_id');
    }

  
}
