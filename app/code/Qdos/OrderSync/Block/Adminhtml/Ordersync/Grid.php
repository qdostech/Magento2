<?php
namespace Qdos\OrderSync\Block\Adminhtml\Ordersync;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
   
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
		\Neo\Mappaymentorder\Model\ResourceModel\Mappaymentorder\Collection $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
		
		$this->_collectionFactory = $collectionFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->setId('ordersyncGrid');
        $this->setDefaultSort('mappaymentorder_id');
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
			$collection = $this->_collectionFactory->load();
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
            'mappaymentorder_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'mappaymentorder_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'payment_method',
            [
                'header' => __('Payment Method'),
                'index' => 'payment_method',
                'class' => 'payment_method'
            ]
        );
        $this->addColumn(
            'order_status',
            [
                'header' => __('Order Status'),
                'sortable' => true,
                'index' => 'order_status',
                'renderer'  => 'Qdos\OrderSync\Block\Adminhtml\Ordersync\Renderer\Orderstatus'
 
            ]
        );
        /*{{CedAddGridColumn}}*/

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

}
