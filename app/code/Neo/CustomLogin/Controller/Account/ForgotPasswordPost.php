<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Neo\CustomLogin\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;

/**
 * ForgotPasswordPost controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForgotPasswordPost extends \Magento\Customer\Controller\Account\ForgotPasswordPost
{
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var Escaper */
    protected $escaper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Neo\CustomLogin\Helper\Data
     */
    public $customLoginHelper;


    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param Escaper $escaper
     * @param \Neo\CustomLogin\Helper\Data $customLoginHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
        \Neo\CustomLogin\Helper\Data $customLoginHelper
    ) {
        $this->customLoginHelper = $customLoginHelper;
        parent::__construct($context,$customerSession,$customerAccountManagement,$escaper);
    }

    /**
     * Forgot customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {

            $main_email = false;
            if($this->customLoginHelper->getForgotPasswordEmail()){
                //$main_email = Mage::helper("customerqdos")->getForgotPasswordEmail();
                $main_email = true;
            }
            if (!\Zend_Validate::is($email, 'EmailAddress') && !$main_email) {
                $this->session->setForgottenEmail($email);
                $this->messageManager->addErrorMessage(__('Please correct the email address.'));
                return $resultRedirect->setPath('*/*/forgotpassword');
            }

            /**
             * below code added to override the functionality of Account holder
             */
            #[Starts here]
            /*
            $Acc_customer = Mage::getModel("customer/customer")
                        ->getCollection()
                        ->addAttributeToFilter("tradin_name", $email)
                        ->addAttributeToSelect("main_email")
                        ->getFirstItem();
            $main_email = "";

            if($Acc_customer->getMainEmail()){
                $main_email = $Acc_customer->getMainEmail();
            }
            */

            try {
                if($main_email){
                    //$customer->setEmail($main_email);
                }
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
            } catch (NoSuchEntityException $exception) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath('*/*/forgotpassword');
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('We\'re unable to send the password reset email.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            }
            $this->messageManager->addSuccessMessage($this->getSuccessMessage($email));
            return $resultRedirect->setPath('*/*/');
        } else {
            $this->messageManager->addErrorMessage(__('Please enter your email.'));
            return $resultRedirect->setPath('*/*/forgotpassword');
        }
    }
}
