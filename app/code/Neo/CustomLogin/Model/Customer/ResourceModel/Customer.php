<?php

namespace Neo\CustomLogin\Model\Customer\ResourceModel;

use Magento\Eav\Model\Entity\VersionControl\AbstractEntity;

class Customer extends \Magento\Customer\Model\ResourceModel\Customer
{
    /**
     * Check customer scope, email and confirmation key before saving
     *
     * @param \Magento\Framework\DataObject $customer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    // protected function _beforeSave(\Magento\Framework\DataObject $customer)
    // {
    //     /** @var \Magento\Customer\Model\Customer $customer */
    //     if ($customer->getStoreId() === null) {
    //         $customer->setStoreId($this->storeManager->getStore()->getId());
    //     }
    //     $customer->getGroupId();

    //     AbstractEntity::_beforeSave($customer);

    //     if (!$customer->getEmail()) {
    //         throw new ValidatorException(__('Please enter a customer email.'));
    //     }

    //     $connection = $this->getConnection();
    //     $bind = ['email' => $customer->getEmail()];

    //     $select = $connection->select()->from(
    //         $this->getEntityTable(),
    //         [$this->getEntityIdField()]
    //     )->where(
    //         'email = :email'
    //     );
    //     if ($customer->getSharingConfig()->isWebsiteScope()) {
    //         $bind['website_id'] = (int)$customer->getWebsiteId();
    //         $select->where('website_id = :website_id');
    //     }
    //     if ($customer->getId()) {
    //         $bind['entity_id'] = (int)$customer->getId();
    //         $select->where('entity_id != :entity_id');
    //     }

    //     // $result = $connection->fetchOne($select, $bind);
    //     // if ($result) {
    //     //     throw new AlreadyExistsException(
    //     //         __('A customer with the same email already exists in an associated website.')
    //     //     );
    //     // }

    //     *
    //      * Unique email address validation for customer using email address while registrtion
    //      * note: customers can also register using username
         
    //     if(!$customer->getIsAccount()) {

    //         $customerModel = $objectManager->create('Magento\Customer\Model\Customer');
    //         $result = $customerModel->getCollection()
    //                                 ->addAttributeToFilter('email', $customer->getEmail())
    //                                 ->addAttributeToFilter("is_account", 0)
    //                                 ->addAttributeToFilter("entity_id",array('neq'=>$customer->getId()))
    //                                 ->addAttributeToFilter("website_id",$customer->getWebsiteId())
    //                                 ->getFirstItem();

    //         if ($result->getId()) {
    //            throw new AlreadyExistsException(
    //                  __('A customer with the same email already exists in an associated website.')
    //             );
    //         }
    //     }

    //     // set confirmation key logic
    //     if ($customer->getForceConfirmed() || $customer->getPasswordHash() == '') {
    //         $customer->setConfirmation(null);
    //     } elseif (!$customer->getId() && $customer->isConfirmationRequired()) {
    //         $customer->setConfirmation($customer->getRandomConfirmationKey());
    //     }
    //     // remove customer confirmation key from database, if empty
    //     if (!$customer->getConfirmation()) {
    //         $customer->setConfirmation(null);
    //     }

    //     $this->_validate($customer);

    //     return $this;
    // }

    public function loadByEmail(\Magento\Customer\Model\Customer $customer, $email)
    {
        $connection = $this->getConnection();
        $bind = ['customer_email' => $email];
        $select = $connection->select()->from(
            $this->getEntityTable(),
            [$this->getEntityIdField()]
        )->where(
            'email = :customer_email'
        );

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            if (!$customer->hasData('website_id')) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('A customer website ID must be specified when using the website scope.')
                );
            }
            $bind['website_id'] = (int)$customer->getWebsiteId();
            $select->where('website_id = :website_id');
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $eavConfig = $objectManager->get('\Magento\Eav\Model\Config');
        $attr =  $eavConfig->getAttribute('customer','is_account');

        if ($attr->getId()) {
            // atttribute exists
              /**
             * below code is used to bypass normal login process
             */
            $CustomerModel = $objectManager->create('Magento\Customer\Model\Customer');

            if(!strpos($email, "@")){
                $customerObj = $objectManager->create('Magento\Customer\Model\ResourceModel\Customer\Collection')
                    ->addAttributeToFilter('tradin_name',$email)
                    ->addAttributeToFilter('is_account',1)
                    ->getFirstItem();

                $customerId = $customerObj->getId();
            }else{
                $customerObj = $objectManager->create('Magento\Customer\Model\ResourceModel\Customer\Collection')
                    ->addAttributeToFilter('email',$email)
                    //->addAttributeToFilter('is_account',0)
                    ->getFirstItem();

                $customerId = $customerObj->getId();
            }
        } else {
            $customerId = $adapter->fetchOne($select, $bind);
        }

        if ($customerId) {
            $this->load($customer, $customerId);
        } else {
            $customer->setData([]);
        }

        return $this;
    }
}