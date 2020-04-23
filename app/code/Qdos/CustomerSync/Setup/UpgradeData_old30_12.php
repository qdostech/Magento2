<?php

namespace Qdos\CustomerSync\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
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
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
 
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
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
    }
}