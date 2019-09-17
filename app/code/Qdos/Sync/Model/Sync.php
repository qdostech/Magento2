<?php

namespace Qdos\Sync\Model;

class Sync extends \Magento\Framework\Model\AbstractModel
{
    const LOG_SUCCESS = 1;
    const LOG_FAIL = 0;
    const LOG_PENDING = 2;
    const LOG_QUEUE = 3;
    const LOG_BACKGROUND = 4;
    const LOG_WARNING = 5;
    const LOG_PARTIAL = 6;

    protected $_storeManager;
    protected $_resourceConfig;
    protected $_logger;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        // \Magento\Store\Model\StoreManagerInterface $storeManager,
        // \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        //\Psr\Log\LoggerInterface $logger,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        // $this->_storeManager = $storeManager;
        // $this->_resourceConfig = $resourceConfig;
        // $this->_logger = $context->getLogger();
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Qdos\Sync\Model\ResourceModel\Sync');
    }

    public function getOptions()
    {
        $type = array('product' => 'IMPORT PRODUCT',
            'import_attribute' => 'IMPORT ATTRIBUTES',
            'category' => 'SYNC CATEGORIES',
            'price' => 'UPDATE PRICES',
            'delete_product' => 'DELETE PRODUCTS',
            'inventory' => 'UPDATE STOCKS',
            'order_status' => 'SYNC ORDER STATUS',
            'order' => 'SYNC ORDER',
            'image' => 'UPDATE IMAGE',
            'position' => 'SYNC PRODUCT POSITION',
            'auto_reindex' => 'AUTO REINDEXING',
            'orderdetails' => 'ORDER DETAILS'
        );
        return $type;
    }

    public function getStatusOptions()
    {
        $options = array(0 => 'Fail',
            1 => 'Success',
            2 => 'Processing',
            3 => 'Queue',
            4 => 'Waiting',
            5 => 'Warning',
            6 => 'Partial');
        return $options;
    }

}