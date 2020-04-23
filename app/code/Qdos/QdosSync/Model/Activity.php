<?php

namespace Qdos\QdosSync\Model;

use Magento\Framework\Exception\SyncException;

/**
 * Synctab sync model
 */
class Activity extends \Magento\Framework\Model\AbstractModel
{
    const LOG_SUCCESS = 1;
    const LOG_FAIL = 0;
    const LOG_PENDING = 2;
    const LOG_QUEUE = 3;
    const LOG_BACKGROUND = 4;
    const LOG_WARNING = 5;

    const EMAIL_ERROR_PATH = 'qdossync_url/email_error';

    public function _construct()
    {
        parent::_construct();
        $this->_statusOptions = $this->getStatusOptions();
    }

    public function getStatusOptions()
    {
        $options = array(0 => 'Fail',
            1 => 'Success',
            2 => 'Processing',
            3 => 'Queue',
            4 => 'Waiting',
            5 => 'Warning');
        return $options;
    }

    public function getActivityTypeByKey($key)
    {
        $type = $this->getStatusOptions();
        return $type[$key];
    }


    /* ravi */
    public function getOptions()
    {
        $type = array('product' => 'IMPORT PRODUCT',
            'attribute' => 'UPDATE PRODUCT ATTRIBUTE',
            'import_attribute' => 'IMPORT ATTRIBUTES',
            'image' => 'UPDATE IMAGE',
            'order' => 'EXPORT ORDER',
            'grouppricing' => 'UPDATE GROUP PRICING',
            'inventory' => 'UPDATE STOCKS',
            'price' => 'UPDATE PRICES',
            'position' => 'UPDATE PRODUCT POSITION',
            'customer_group' => 'SYNC CUSTOMER GROUP',
            'customer' => 'IMPORT CUSTOMER',
            'category' => 'SYNC CATEGORIES',
            'voucher' => 'EXPORT VOUCHER',
            'producer' => 'IMPORT PRODUCERS',
            'event' => 'IMPORT EVENTS',
            'store' => 'IMPORT STORES',
            'store_inventory' => 'IMPORT INVENTORY',
            'update_attribute_product' => 'UPDATE ATTRIBUTE FOR ALL PRODUCTS',
            'delete_attribute' => 'DELETE ATTRIBUTE',
            'delete_product' => 'DELETE PRODUCT',
            'export_product' => 'EXPORT PRODUCTS',
            'export_category' => 'EXPORT CATEGORIES',
            'export_attribute' => 'EXPORT ATTRIBUTES',
            'export_customer_group' => 'EXPORT CUSTOMER GROUP',
            'export_customer' => 'EXPORT CUSTOMER',
            'order_status' => 'SYNC ORDER STATUS',
            'SYNC GRAPE' => 'SYNC GRAPE',
            'get_location'=>'IMPORT LOCATION',
            'get_location_qty'=>'IMPORT LOCATION QTY',
            'product_image_delete'=>'DELETED IMAGES'
        );
        return $type;
    }

    public function getFilterOptions($likeQuery = '')
    {
        $options = $this->getOptions();
        $result = array();
        foreach ($options as $k => $value) {
            if (strpos($k, $likeQuery) !== false) {
                $result[$k] = $value;
            }
        }
        return $result;
    }
}