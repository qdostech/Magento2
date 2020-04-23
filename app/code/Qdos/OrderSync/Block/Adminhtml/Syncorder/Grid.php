<?php
namespace Qdos\OrderSync\Block\Adminhtml\Syncorder;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_resource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Config $statusConfig,
        \Magento\Payment\Model\Config $paymentConfig,
        // \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->_statusConfig = $statusConfig;
        $this->_paymentConfig = $paymentConfig;
        // $this->scopeConfig = $scopeConfig;
        $this->_resource = $resource;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->setId('productGrid');
        $this->setDefaultSort('id');
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
		try{
            $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            $orderSyncStatusTable = $this->_resource->getTableName('order_sync_status');
            $collection = $this->orderCollectionFactory->create(); 
            $collection->join(array('payment'=>'sales_order_payment'),'main_table.entity_id=parent_id','method');
            $collection->getSelect()->joinLeft(array('ordersyncstatus'=>$orderSyncStatusTable),'main_table.entity_id=ordersyncstatus.order_id',array('sync_status'=>'sync_status'));
            // print_r($collection->getData());die;
            $this->setCollection($collection);
            parent::_prepareCollection();
          
            return $this;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();die;
		}
    }

   /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'increment_id',
            [
                'header' => __('Order #'),
                'type' => 'number',
                'index' => 'increment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'store_id',
            [
                'header' => __('Purchased From (Store)'),
                'index' => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Purchased On'),
                'index' => 'created_at',
                'type' => 'datetime',
                'width' => '100px'
            ]
        );
        $this->addColumn(
            'billing_name',
            [
                'header' => __('Bill to Name'),
                'index' => 'billing_name',
            ]
        );
        $this->addColumn(
            'shipping_name',
            [
                'header' => __('Ship to Name'),
                'index' => 'shipping_name'            ]
        );
        
        $payments = $this->_paymentConfig->getActiveMethods();
        $methods = array();
        foreach ($payments as $paymentCode=>$paymentModel)
        {
            $paymentTitle = $this->_scopeConfig->getValue('payment/'.$paymentCode.'/title',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $methods[$paymentCode] = $paymentTitle;
        }
        $methods['paypal_standard'] = "Paypal Standard";
        
        $this->addColumn('method', array(
            'header'    => __('Payment Method'),
            'index'     => 'method',
            'type'      => 'options',
            'options'       => $methods
        ));

        $this->addColumn('base_grand_total', array(
            'header' => __('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => __('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => __('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => $this->_statusConfig->getStatuses(),
        ));

        $this->addColumn('sync_status', array(
            'header' => __('Sync Status'),
            'index' => 'sync_status',
            'type'  => 'options',
            'width' => '70px',
            'options' => array('no'=>'No', 'yes'=>'Yes'),
            'renderer'  => 'Qdos\OrderSync\Block\Adminhtml\Syncorder\Renderer\Syncstatus'
            // 'filter_index' => 'ordersyncstatus.sync_status',
        ));

        $this->addColumn('action',
            array(
                'header'    => __('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => __('Sync'),
                        'url'     => array('base'=>'*/syncorder/sync'),
                        'field'   => 'order_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

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
        $this->setMassactionIdField('increment_id');
        $this->getMassactionBlock()->setFormFieldName('increment_id');

        $this->getMassactionBlock()->addItem(
            'Sync',
            array(
                'label' => __('Sync'),
                'url' => $this->getUrl('ordersync/syncorder/sync'),
                'confirm' => __('Are you sure?')
            )
        );
        return $this;
    }
}
