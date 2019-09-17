<?php
namespace Qdos\Syncevent\Block\Adminhtml\Syncevent;


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
		\Qdos\Syncevent\Model\ResourceModel\Syncevent\Collection $collectionFactory,
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
			
			
			$collection =$this->_collectionFactory->load();

		  

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
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		$this->addColumn(
            'event_name',
            [
                'header' => __('Event Name'),
                'index' => 'event_name',
                'class' => 'event_name'
            ]
        );
		$this->addColumn(
            'objective',
            [
                'header' => __('Objective'),
                'index' => 'objective',
                'class' => 'objective'
            ]
        );
		/*$this->addColumn(
            'event_desc',
            [
                'header' => __('event_desc'),
                'index' => 'event_desc',
                'class' => 'event_desc'
            ]
        );*/
		$this->addColumn(
            'start_dt',
            [
                'header' => __('start date'),
                'index' => 'start_dt',
                'type' => 'date',
            ]
        );
		$this->addColumn(
            'end_dt',
            [
                'header' => __('end date'),
                'index' => 'end_dt',
                'type' => 'date',
            ]
        );
		$this->addColumn(
            'channel',
            [
                'header' => __('Channel'),
                'index' => 'channel',
                'class' => 'channel'
            ]
        );
		$this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type',
                'class' => 'type'
            ]
        );
		$this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'class' => 'status'
            ]
        );
		$this->addColumn(
            'cost',
            [
                'header' => __('Cost'),
                'index' => 'cost',
                'class' => 'cost'
            ]
        );
		/*$this->addColumn(
            'approver',
            [
                'header' => __('approver'),
                'index' => 'approver',
                'class' => 'approver'
            ]
        );
		$this->addColumn(
            'approved_dt',
            [
                'header' => __('approved_dt'),
                'index' => 'approved_dt',
                'type' => 'date',
            ]
        );
		$this->addColumn(
            'reminder_flg',
            [
                'header' => __('reminder_flg'),
                'index' => 'reminder_flg',
                'class' => 'reminder_flg'
            ]
        );
		$this->addColumn(
            'reminder_dt',
            [
                'header' => __('reminder_dt'),
                'index' => 'reminder_dt',
                'type' => 'date',
            ]
        );
		$this->addColumn(
            'req_confirm_dt',
            [
                'header' => __('req_confirm_dt'),
                'index' => 'req_confirm_dt',
                'type' => 'date',
            ]
        );
		$this->addColumn(
            'location',
            [
                'header' => __('location'),
                'index' => 'location',
                'class' => 'location'
            ]
        );
		$this->addColumn(
            'cancel_allowed',
            [
                'header' => __('cancel_allowed'),
                'index' => 'cancel_allowed',
                'class' => 'cancel_allowed'
            ]
        );
		$this->addColumn(
            'max_capacity',
            [
                'header' => __('max_capacity'),
                'index' => 'max_capacity',
                'class' => 'max_capacity'
            ]
        );
		$this->addColumn(
            'waitlist_allowed',
            [
                'header' => __('waitlist_allowed'),
                'index' => 'waitlist_allowed',
                'class' => 'waitlist_allowed'
            ]
        );
		$this->addColumn(
            'waitlist_number',
            [
                'header' => __('waitlist_number'),
                'index' => 'waitlist_number',
                'class' => 'waitlist_number'
            ]
        );
		$this->addColumn(
            'deposit_req',
            [
                'header' => __('deposit_req'),
                'index' => 'deposit_req',
                'class' => 'deposit_req'
            ]
        );
		$this->addColumn(
            'on_day_register',
            [
                'header' => __('on_day_register'),
                'index' => 'on_day_register',
                'class' => 'on_day_register'
            ]
        );
		$this->addColumn(
            'position_id',
            [
                'header' => __('position_id'),
                'index' => 'position_id',
                'class' => 'position_id'
            ]
        );
		$this->addColumn(
            'event_owner_id',
            [
                'header' => __('event_owner_id'),
                'index' => 'event_owner_id',
                'class' => 'event_owner_id'
            ]
        );
		$this->addColumn(
            'campaign_id',
            [
                'header' => __('campaign_id'),
                'index' => 'campaign_id',
                'class' => 'campaign_id'
            ]
        );
		$this->addColumn(
            'event_ref',
            [
                'header' => __('event_ref'),
                'index' => 'event_ref',
                'class' => 'event_ref'
            ]
        );
		$this->addColumn(
            'product_id',
            [
                'header' => __('product_id'),
                'index' => 'product_id',
                'class' => 'product_id'
            ]
        );
		$this->addColumn(
            'parent_id',
            [
                'header' => __('parent_id'),
                'index' => 'parent_id',
                'class' => 'parent_id'
            ]
        );
		$this->addColumn(
            'annual_event',
            [
                'header' => __('annual_event'),
                'index' => 'annual_event',
                'class' => 'annual_event'
            ]
        );
		$this->addColumn(
            'loc_id',
            [
                'header' => __('loc_id'),
                'index' => 'loc_id',
                'class' => 'loc_id'
            ]
        );
		$this->addColumn(
            'contact_id',
            [
                'header' => __('contact_id'),
                'index' => 'contact_id',
                'class' => 'contact_id'
            ]
        );
		$this->addColumn(
            'first_account_id',
            [
                'header' => __('first_account_id'),
                'index' => 'first_account_id',
                'class' => 'first_account_id'
            ]
        );
		$this->addColumn(
            'second_account_id',
            [
                'header' => __('second_account_id'),
                'index' => 'second_account_id',
                'class' => 'second_account_id'
            ]
        );
		$this->addColumn(
            'acnt_rel_type',
            [
                'header' => __('acnt_rel_type'),
                'index' => 'acnt_rel_type',
                'class' => 'acnt_rel_type'
            ]
        );
		$this->addColumn(
            'venue',
            [
                'header' => __('venue'),
                'index' => 'venue',
                'class' => 'venue'
            ]
        );
		$this->addColumn(
            'assess_templ_id',
            [
                'header' => __('assess_templ_id'),
                'index' => 'assess_templ_id',
                'class' => 'assess_templ_id'
            ]
        );
		$this->addColumn(
            'dates_tbc',
            [
                'header' => __('dates_tbc'),
                'index' => 'dates_tbc',
                'class' => 'dates_tbc'
            ]
        );
		$this->addColumn(
            'event_short_name',
            [
                'header' => __('event_short_name'),
                'index' => 'event_short_name',
                'class' => 'event_short_name'
            ]
        );
		$this->addColumn(
            'html_short',
            [
                'header' => __('html_short'),
                'index' => 'html_short',
                'class' => 'html_short'
            ]
        );
		$this->addColumn(
            'html_long',
            [
                'header' => __('html_long'),
                'index' => 'html_long',
                'class' => 'html_long'
            ]
        );
		$this->addColumn(
            'checkin_date',
            [
                'header' => __('checkin_date'),
                'index' => 'checkin_date',
                'type' => 'date',
            ]
        );
		$this->addColumn(
            'checkout_date',
            [
                'header' => __('checkout_date'),
                'index' => 'checkout_date',
                'type' => 'date',
            ]
        );
		$this->addColumn(
            'location_name',
            [
                'header' => __('location_name'),
                'index' => 'location_name',
                'class' => 'location_name'
            ]
        );
		$this->addColumn(
            'colour',
            [
                'header' => __('colour'),
                'index' => 'colour',
                'class' => 'colour'
            ]
        );
		$this->addColumn(
            'sku',
            [
                'header' => __('sku'),
                'index' => 'sku',
                'class' => 'sku'
            ]
        );*/
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
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Delete'),
                'url' => $this->getUrl('syncevent/*/massDelete'),
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
        return $this->getUrl('syncevent/*/index', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'syncevent/*/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
    }
}
