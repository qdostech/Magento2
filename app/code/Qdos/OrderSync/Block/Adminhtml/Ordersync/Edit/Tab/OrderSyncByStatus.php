<?php
namespace Qdos\OrderSync\Block\Adminhtml\Ordersync\Edit\Tab;
class OrderSyncByStatus extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
		/* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('ordersync_ordersync');
		$isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Order sync by status')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

		$fieldset->addField(
            'order_sync_status_id',
            'text',
            array(
                'name' => 'order_sync_status_id',
                'label' => __('id'),
                'title' => __('id'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'sync_status',
            'text',
            array(
                'name' => 'sync_status',
                'label' => __('sync status'),
                'title' => __('sync status'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'payment_method',
            'text',
            array(
                'name' => 'payment_method',
                'label' => __('payment method'),
                'title' => __('payment method'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'created_time',
            'date',
            array(
                'name' => 'created_time',
                'label' => __('created_at'),
                'title' => __('created_at'),
				'format' => 'yy-mm-dd',
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'update_time',
            'date',
            array(
                'name' => 'update_time',
                'label' => __('updated_at'),
                'title' => __('updated_at'),
				'format' => 'yy-mm-dd',
                /*'required' => true,*/
            )
        );
		/*{{CedAddFormField}}*/
        
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();   
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Order sync by status');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Order sync by status');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
