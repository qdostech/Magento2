<?php
namespace Qdos\QdosSync\Block\Adminhtml\Storemapping\Edit\Tab;
class StoreMapping extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    //public $options;

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
        \Qdos\QdosSync\Helper\Data $options,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        $this->options = $options;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
            /*$test = $this->helper->toOptionMultiSelectArray();
            echo "<pre>";
            print_r(expression)*/
		/* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('qdossync_storemapping');
		$isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Store Mapping')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

		$fieldset->addField(
            'store_id',
            'select',
            array(
                'name' => 'store_id',
                'label' => __('store id'),
                'title' => __('store id'),
                /*'required' => true,*/
                'values' => $this->_systemStore->getStoreValuesForForm(false,true), // set store view in the admin panel
            )
        );
		$fieldset->addField(
            'sync_type',
            'multiselect',
            array(
                'name' => 'sync_type',
                'label' => __('sync type'),
                'title' => __('sync type'),
                'required' => true,  
                'values' => $this->options->toOptionMultiSelectArray(), // set all the multiselect options
                       
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
        return __('Store Mapping');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Store Mapping');
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
