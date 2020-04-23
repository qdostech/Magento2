<?php
namespace Qdos\Syncproducer\Block\Adminhtml\Logs;


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
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
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
        \Qdos\QdosSync\Model\ResourceModel\Log\Collection $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Qdos\QdosSync\Model\Activity $activitySync,
        \Qdos\Sync\Model\Sync $qdosSync,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->moduleManager = $moduleManager;
        $this->activitySync = $activitySync;
        $this->_qdosSync = $qdosSync;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productlocationqtylogsGrid');
        $this->setDefaultSort('log_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setOptions($this->activitySync->getFilterOptions('producer'));
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
        try{
            $inArr = array();
              foreach ($this->getOptions() as $k=>$option){
                  $inArr[] = $k;
              }
            //$collection = $this->_collectionFactory->load();
            $collection = $this->_collectionFactory->load()
              ->addFieldToFilter('activity_type',array('in'=>$inArr));

             
            $this->setCollection($collection);
            parent::_prepareCollection();
            return $this;
        }catch(Exception $e){
            echo $e->getMessage();die;
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
            'log_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'log_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'activity_type',
            [
                'header' => __('Activity'),
                'index' => 'activity_type',
                'type' => 'options',
                'class' => 'activity',
                'renderer' => 'Qdos\QdosSync\Block\Adminhtml\Sync\Renderer\Span',
                'options' => $this->getOptions()
            ]
        );
        $this->addColumn(
            'ip_address',
            [
                'header' => __('From IP'),
                'index' => 'ip_address',
                'class' => 'fromip'
            ]
        );
        $this->addColumn(
            'start_time',
            [
                'header' => __('Start'),
                'index' => 'start_time',
                'type' => 'date'
            ]
        );
        $this->addColumn(
            'end_time',
            [
                'header' => __('Finish'),
                'index' => 'end_time',
                'type' => 'date'
            ]
        );
        $this->addColumn('status', array(
            'header' => __('Status'),
            'align' => 'center',
            'index' => 'status',
            'type' => 'options',
            'renderer' => 'Qdos\QdosSync\Block\Adminhtml\Grid\Column\Renderer\Status',
            'options' => $this->_qdosSync->getStatusOptions()
        ));
        $this->addColumn(
            'description',
            [
                'type' => 'text',
                'filter' => false,
                'header' => __('Log Details'),
                'renderer'  => 'Qdos\QdosSync\Block\Adminhtml\Grid\Column\Renderer\Log'
            ]
        );

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
    // protected function _prepareMassaction()
    // {
    //     $this->setMassactionIdField('id');
    //     $this->getMassactionBlock()->setFormFieldName('id');

    //     $this->getMassactionBlock()->addItem(
    //         'delete',
    //         array(
    //             'label' => __('Delete'),
    //             'url' => $this->getUrl('Syncevent/*/massDelete'),
    //             'confirm' => __('Are you sure?')
    //         )
    //     );
    //     return $this;
    // }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/index', ['_current' => true]);
    }

    public function getStatus(){
        return 'Pending';
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
    }

}
