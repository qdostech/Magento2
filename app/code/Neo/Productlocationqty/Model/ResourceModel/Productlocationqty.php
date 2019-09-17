<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */
namespace Neo\Productlocationqty\Model\ResourceModel;


class Productlocationqty extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('qdos_quote_product_qty', 'id');
    }

  
}
