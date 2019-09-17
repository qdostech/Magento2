<?php
namespace Qdos\Syncproducer\Block\Adminhtml\Syncproducer\Edit\Tab;
class SyncProducer extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $model = $this->_coreRegistry->registry('syncproducer_syncproducer');
		$isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Sync Producer')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

	/*	$fieldset->addField(
            'producer_id',
            'text',
            array(
                'name' => 'producer_id',
                'label' => __('producer_id'),
                'title' => __('producer_id')
            )
        );*/
		$fieldset->addField(
            'producer_name',
            'text',
            array(
                'name' => 'producer_name',
                'label' => __('producer_name'),
                'title' => __('producer_name'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'description',
            'text',
            array(
                'name' => 'description',
                'label' => __('description'),
                'title' => __('description'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'meta_title',
            'text',
            array(
                'name' => 'meta_title',
                'label' => __('meta_title'),
                'title' => __('meta_title'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'meta_keyword',
            'text',
            array(
                'name' => 'meta_keyword',
                'label' => __('meta_keyword'),
                'title' => __('meta_keyword'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'meta_description',
            'text',
            array(
                'name' => 'meta_description',
                'label' => __('meta_description'),
                'title' => __('meta_description'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'producer_rich_name',
            'text',
            array(
                'name' => 'producer_rich_name',
                'label' => __('producer_rich_name'),
                'title' => __('producer_rich_name'),
                /*'required' => true,*/
            )
        );
		$fieldset->addField(
            'image_name',
            'text',
            array(
                'name' => 'image_name',
                'label' => __('image_name'),
                'title' => __('image_name'),
                /*'required' => true,*/
            )
        );
		
        
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
        return __('Sync Producer');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Sync Producer');
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
