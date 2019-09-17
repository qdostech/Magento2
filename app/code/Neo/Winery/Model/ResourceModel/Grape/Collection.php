<?php
 /**
 * Grid Grid Collection.
 * @category    Webkul
 * @author      Webkul Software Private Limited
 */
namespace Neo\Winery\Model\ResourceModel\Grape;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init('Neo\Winery\Model\Log', 'Neo\Winery\Model\ResourceModel\Log');
    }
}