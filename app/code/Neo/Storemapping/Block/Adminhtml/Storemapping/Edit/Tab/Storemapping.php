<?php
namespace Neo\Storemapping\Block\Adminhtml\Storemapping\Edit\Tab;
class Storemapping extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        \Magento\Payment\Model\Config $paymentConfig,
        \Neo\Storemapping\Model\Storemapping $storemappingModel,
        \Magento\Sales\Model\Order\Status $orderStatus,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        $this->paymentConfig = $paymentConfig;
        $this->orderStatus = $orderStatus;
        $this->storemappingModel = $storemappingModel;
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
        $model = $this->_coreRegistry->registry('neo_storemapping');
		    $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Storemapping')));

        if ($model->getStoremappingId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

        $orderStatus = '';
        
        // $fieldset->addField('order_status', 'multiselect', array(
        //     'name'      => 'order_status',
        //     'label'     => __('Order Status'),
        //     'required'  => true,
        //     'values'    => $orderStatus
        // ));

        // $fieldset->addField('order_status_invoice', 'multiselect', array(
        //     'name'      => 'order_status_invoice',
        //     'label'     => __('Invoice For Order Status'),
        //     'required'  => false,
        //     'values'    => $orderStatus
        // ));
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();    
        $system_store = $objectManager->get("\Magento\Store\Model\System\Store");

        $fieldset->addField('store_id', 'select', array(
            'label'     => __('Select Store View'),
            'name'      => 'store_id',
            'values'    => $system_store->getStoreValuesForForm(false, true),
        ));

        $arr = $objectManager->get("\Neo\Storemapping\Model\Storemapping")->getOptionArray();

        $fieldset->addField('sync_type', 'multiselect', array(
           'label'     => __('Select Sync Type'),
           'name'      => 'sync_type',
           'values'    => $arr,
        ));
		
		/*{{CedAddFormField}}*/

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
        return __('Storemapping');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Storemapping');
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
