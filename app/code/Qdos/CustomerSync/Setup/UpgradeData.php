<?php

namespace Qdos\CustomerSync\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
 
/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
     /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;
 
    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory,EavSetupFactory $eavSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }
 
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $customerSetupFactory = $this->customerSetupFactory->create(['setup' => $setup]);
        /** @var EavSetup $eavSetup */

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '1.0.1') < 0){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSetup = $objectManager->create('Qdos\CustomerSync\Setup\CustomerSetup');
            $customerSetup->installAttributes($customerSetup);
		}

        if (version_compare($context->getVersion(), '1.0.2') < 0){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $salesSetup = $objectManager->create('Magento\Sales\Setup\SalesSetup');
            $salesSetup->addAttribute('order', 'pay_by_account', ['type' =>'varchar']);
            $salesSetup->addAttribute('order', 'is_account', ['type' =>'int']);
            $salesSetup->addAttribute('order', 'checkout_person', ['type' =>'varchar']);

            $quoteSetup = $objectManager->create('Magento\Quote\Setup\QuoteSetup');
            $quoteSetup->addAttribute('quote', 'pay_by_account', ['type' =>'varchar']);
            $quoteSetup->addAttribute('quote', 'is_account', ['type' =>'int']);
            $quoteSetup->addAttribute('quote', 'checkout_person', ['type' =>'varchar']);
        }
         if (version_compare($context->getVersion(), '1.0.3') < 0)
         {
            //remove is_active attribute

           // $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY,'is_active');//



            $customerSetupFactory -> addAttribute(\Magento\Customer\Model\Customer::ENTITY,
            'is_enable',
            [
            'label' => 'Is Active',
            'system' => 0,
            'position' => 999,
            'sort_order' =>999,
            'visible' =>  true,
            'note' => '',
                

                        'type' => 'int',
                        'input' => 'boolean',
                        'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            
            ]
            );

        $customerSetupFactory->getEavConfig() -> getAttribute('customer', 'is_enable')->setData('is_user_defined',1)->setData('is_required',0)->setData('default_value','')->setData('used_in_forms', ['adminhtml_customer']) -> save();
         }
    }
}