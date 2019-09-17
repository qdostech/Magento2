<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */

namespace Neo\Storemapping\Model;

class Storemapping extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Neo\Storemapping\Model\ResourceModel\Storemapping');
    }

    public function getSyncsList()
    {
        return $arrSyncs = array('Category', 'Attribute', 'Product', 'Stock', 'Price', 'Delete Product', 'Order', 'Manual Sync Products');      
    }

    public function getOptionArray()
    {

        $syncsList = $this->getSyncsList();

        $retVal = array();

        foreach ($syncsList as $key => $value) {
            $retVal[] = array(
                          'value'     => $key,
                          'label'     => __($value),
                      );
        }

        return $retVal;     
    }
}