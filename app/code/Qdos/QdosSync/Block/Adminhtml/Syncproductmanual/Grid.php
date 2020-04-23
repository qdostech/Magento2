<?php

namespace Qdos\QdosSync\Block\Adminhtml\Syncproductmanual;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;
    protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Statsync_statusus $status
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\Type $type,
        \Qdos\Sync\Model\Sync $qdosSync,
        array $data = []
    )
    {

        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->moduleManager = $moduleManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_productType = $type;
        $this->_qdosSync = $qdosSync;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('productGrid');
        $this->setDefaultSort('log_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);

    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        try {
            $store = $this->_getStore();
            $collection = $this->_productCollectionFactory->create()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('type_id')
                ->addAttributeToSelect('last_sync')
                ->addAttributeToSelect('sync_status')
                ->addAttributeToSelect('last_log_id');


            if ($store->getId()) {
                $adminStore = 0;
                $collection->addStoreFilter($store);
                $collection->joinAttribute(
                    'name',
                    'catalog_product/name',
                    'entity_id',
                    null,
                    'inner',
                    $adminStore
                );
                $collection->joinAttribute(
                    'custom_name',
                    'catalog_product/name',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                );
            }

            /*if (!$this->_storeManager->isSingleStoreMode() && $store->getId()){
                $collection->addFieldToFilter('store_id', $store->getId());
            }*/

            $this->setCollection($collection);
            $this->getCollection()->addWebsiteNamesToResult();
            //echo "<pre>";print_r($collection->getData());exit;

            parent::_prepareCollection();

            return $this;
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'name'
            ]
        );
        $this->addColumn('type',
            array(
                'header' => __('Type'),
                'width' => '150px',
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->_productType->getOptionArray(),
            ));

        $this->addColumn('sku', array(
            'header' => __('SKU'),
            'align' => 'left',
            'index' => 'sku',
            'width' => '50px'
        ));

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'websites',
                [
                    'header' => __('Websites'),
                    'sortable' => false,
                    'index' => 'websites',
                    'type' => 'options',
                    'options' => $this->_websiteFactory->create()->getCollection()->toOptionHash(),
                    'header_css_class' => 'col-websites',
                    'column_css_class' => 'col-websites'
                ]
            );
        }

        $this->addColumn('last_sync', array(
            'header' => __('Last Sync'),
            'align' => 'center',
            'index' => 'last_sync',
            //'renderer' => 'Qdos\QdosSync\Block\Adminhtml\Sync\Renderer\Syncdatetime',
            'type' => 'datetime',
            'width' => '150px'
        ));

        $this->addColumn('sync_status', array(
            'width' => '100px',
            'header' => __('Status'),
            'align' => 'center',
            'index' => 'sync_status',
            'filter_condition_callback' => [$this, 'filterSyncStatus'],
            'type' => 'options',
            'renderer' => 'Qdos\QdosSync\Block\Adminhtml\Renderer\Status',
            'options' => $this->_qdosSync->getStatusOptions()
        ));

        $this->addColumn('log', array(
            'align' => 'center',
            'header' => __('Log Detail'),
            'width' => '100px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('View Log'),
                    'url' => 'javascript:void(0)'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
            'renderer' => 'Qdos\QdosSync\Block\Adminhtml\Renderer\Log'
        ));

        $store = $this->_getStore();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $syncPermissions = $this->_scopeConfig->getValue('qdosConfig/permissions/manual_sync_product');

        $syncPermissions2 = array();
        $arrSyncPerm = $objectManager->create('\Qdos\QdosSync\Model\Storemapping')
                       ->getCollection()
                       ->addFieldToSelect('sync_type')
                       ->addFieldToFilter('store_id', $store->getId())
                       ->load()
                       ->getData();
                       
        if (!empty($arrSyncPerm[0]['sync_type'])){
            $syncPermissions2 = explode(',', $arrSyncPerm[0]['sync_type']);
        }

        if ($syncPermissions && in_array('Manual Sync Product', $syncPermissions2)) {
            $this->addColumn('action',
                array(
                    'header' => __('Action'),
                    'width' => '100',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => array(
                        array(
                            'caption' => __('Sync'),
                            'url' => array('base' => '*/*/sync/store/' . $store->getId()),
                            'field' => 'sync'
                        )
                    ),
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true,
                )
            );
        }

        /*{{CedAddGridColumn}}*/
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Delete'),
                'url' => $this->getUrl('qdossync/*/massDelete'),
                'confirm' => __('Are you sure?')
            )
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('qdossync/*/index', ['_current' => true]);
    }

    public function getStatus()
    {
        return 'Pending';
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'catalog/product/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
    }

    protected function filterSyncStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $collection->getSelect()->where("`e`.`sync_status` = ?", "$value");
        //echo $collection->getSelect();exit;
        return $this;
    }
}
