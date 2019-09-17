<?php

namespace Neo\Mappaymentorder\Block\Adminhtml\Mappaymentorder\Edit\Tab;
class Mappaymentorder extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        \Neo\Mappaymentorder\Model\Mappaymentorder $mappaymentorderModel,
        \Magento\Sales\Model\Order\Status $orderStatus,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        array $data = array()
    )
    {
        $this->_systemStore = $systemStore;
        $this->paymentConfig = $paymentConfig;
        $this->orderStatus = $orderStatus;
        $this->mappaymentorderModel = $mappaymentorderModel;
        $this->date = $date;
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
        $model = $this->_coreRegistry->registry('neo_mappaymentorder');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Mappaymentorder')));

        if ($model->getMappaymentorderId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'mappaymentorder_id'));
        }

        $allAvailablePaymentMethods = $this->paymentConfig->getActiveMethods();
        $methods = array(array('value' => '', 'label' => __('--Please Select--')));

        if ($this->getRequest()->getParam('mappaymentorder_id')) {
            $modelId = $this->getRequest()->getParam('mappaymentorder_id');
            $model = $this->mappaymentorderModel->load($modelId);
            foreach ($allAvailablePaymentMethods as $paymentCode => $paymentModel) {
                $paymentTitle = $this->_scopeConfig->getValue('payment/' . $paymentCode . '/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $methods[$paymentCode] = array(
                    'label' => $paymentTitle,
                    'value' => $paymentCode,
                );
            }


            $fieldset->addField('payment_method', 'select', array(
                'label' => __('Payment Method'),
                'name' => 'payment_method',
                //'disabled' => true,
                'required' => true,
                'values' => $methods
            ));
        } else {
            $mappaymentorderModel = $this->mappaymentorderModel->getCollection()->load()->getData();
            $addedPayments = array();
            foreach ($mappaymentorderModel as $key => $value) {
                $addedPayments[] = $value['payment_method'];
            }

            foreach ($allAvailablePaymentMethods as $paymentCode => $paymentModel) {
                $paymentTitle = $this->_scopeConfig->getValue('payment/' . $paymentCode . '/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if (!in_array($paymentCode, $addedPayments)) {
                    $methods[$paymentCode] = array(
                        'label' => $paymentTitle,
                        'value' => $paymentCode,
                    );
                }
            }
            $fieldset->addField('payment_method', 'select', array(
                'label' => __('Payment Method'),
                'name' => 'payment_method',
                'required' => true,
                'values' => $methods
            ));
        }

        $allOrderStatus = $this->orderStatus->getResourceCollection()->getData();
        $methods = array(array('value' => '', 'label' => __('--Please Select--')));
        foreach ($allOrderStatus as $key => $status) {

            $orderStatus[$status['status']] = array(
                'label' => $status['label'],
                'value' => $status['status'],
            );
        }

        $fieldset->addField('order_status', 'multiselect', array(
            'name' => 'order_status',
            'label' => __('Order Status'),
            'required' => true,
            'values' => $orderStatus
        ));

        $fieldset->addField('order_status_invoice', 'multiselect', array(
            'name' => 'order_status_invoice',
            'label' => __('Invoice For Order Status'),
            'required' => false,
            'values' => $orderStatus
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
        return __('Mappaymentorder');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Mappaymentorder');
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
