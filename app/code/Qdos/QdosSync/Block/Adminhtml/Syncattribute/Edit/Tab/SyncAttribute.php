<?php
namespace Qdos\QdosSync\Block\Adminhtml\Syncattribute\Edit\Tab;
class SyncAttribute extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $model = $this->_coreRegistry->registry('qdossync_syncattribute');
		$isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Sync Attribute')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

		$fieldset->addField(
            'activity',
            'text',
            array(
                'name' => 'activity',
                'label' => __('activity'),
                'title' => __('activity'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'fromip',
            'text',
            array(
                'name' => 'fromip',
                'label' => __('from ip'),
                'title' => __('from ip'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'start',
            'date',
            array(
                'name' => 'start',
                'label' => __('start'),
                'title' => __('start'),
				'format' => 'yy-mm-dd',
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'finish',
            'date',
            array(
                'name' => 'finish',
                'label' => __('finish'),
                'title' => __('finish'),
				'format' => 'yy-mm-dd',
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'websites',
            'text',
            array(
                'name' => 'websites',
                'label' => __('websites'),
                'title' => __('websites'),
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
            'logdetails',
            'text',
            array(
                'name' => 'logdetails',
                'label' => __('log details'),
                'title' => __('log details'),
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
        return __('Sync Attribute');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Sync Attribute');
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
