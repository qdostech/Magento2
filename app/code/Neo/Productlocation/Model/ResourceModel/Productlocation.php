<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */
namespace Neo\Productlocation\Model\ResourceModel;


class Productlocation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('qdos_product_location', 'id');
    }

  
}
