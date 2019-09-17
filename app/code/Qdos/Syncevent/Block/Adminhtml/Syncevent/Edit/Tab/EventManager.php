<?php
namespace Qdos\Syncevent\Block\Adminhtml\Syncevent\Edit\Tab;
class EventManager extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $model = $this->_coreRegistry->registry('syncevent_syncevent');
		$isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Event Manager')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

		$fieldset->addField(
            'event_name',
            'text',
            array(
                'name' => 'event_name',
                'label' => __('event name'),
                'title' => __('event name'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'objective',
            'text',
            array(
                'name' => 'objective',
                'label' => __('objective'),
                'title' => __('objective'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'start_dt',
            'date',
            array(
                'name' => 'start_dt',
                'label' => __('start date'),
                'title' => __('start date'),
				'format' => 'yy-mm-dd',
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'end_dt',
            'date',
            array(
                'name' => 'end_dt',
                'label' => __('end date'),
                'title' => __('end date'),
				'format' => 'yy-mm-dd',
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'channel',
            'text',
            array(
                'name' => 'channel',
                'label' => __('channel'),
                'title' => __('channel'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'type',
            'text',
            array(
                'name' => 'type',
                'label' => __('type'),
                'title' => __('type'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'status',
            'text',
            array(
                'name' => 'status',
                'label' => __('status'),
                'title' => __('status'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'cost',
            'text',
            array(
                'name' => 'cost',
                'label' => __('cost'),
                'title' => __('cost'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'event_desc',
            'text',
            array(
                'name' => 'event_desc',
                'label' => __('event desc'),
                'title' => __('event desc'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'approver',
            'text',
            array(
                'name' => 'approver',
                'label' => __('approver'),
                'title' => __('approver'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'approved_dt',
            'date',
            array(
                'name' => 'approved_dt',
                'label' => __('approver date'),
                'title' => __('approver date'),
				'format' => 'yy-mm-dd',
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'reminder_flg',
            'text',
            array(
                'name' => 'reminder_flg',
                'label' => __('reminder flag'),
                'title' => __('reminder flag'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'reminder_dt',
            'date',
            array(
                'name' => 'reminder_dt',
                'label' => __('reminder date'),
                'title' => __('reminder date'),
				'format' => 'yy-mm-dd',
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'req_confirm_dt',
            'date',
            array(
                'name' => 'req_confirm_dt',
                'label' => __('req conf date'),
                'title' => __('req conf date'),
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
        return __('Event Manager');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Event Manager');
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
