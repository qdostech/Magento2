<?php

namespace Qdos\CustomerSync\Setup;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;

class CustomerSetup extends EavSetup {

	protected $eavConfig;

	public function __construct(
		ModuleDataSetupInterface $setup,
		Context $context,
		CacheInterface $cache,
		CollectionFactory $attrGroupCollectionFactory,
		Config $eavConfig
		) {
		$this -> eavConfig = $eavConfig;
		parent :: __construct($setup, $context, $cache, $attrGroupCollectionFactory);
	} 

	public function installAttributes($customerSetup) {
		$this -> installCustomerAttributes($customerSetup);
		$this -> installCustomerAddressAttributes($customerSetup);
	} 

	public function installCustomerAttributes($customerSetup) {
			

		$customerSetup -> addAttribute(\Magento\Customer\Model\Customer::ENTITY,
			'login_email',
			[
			'label' => 'Login Email',
			'system' => 0,
			'position' => 999,
            'sort_order' =>999,
            'visible' =>  true,
			'note' => '',
				

                        'type' => 'varchar',
                        'input' => 'text',
			
			]
			);

		$customerSetup -> getEavConfig() -> getAttribute('customer', 'login_email')->setData('is_user_defined',1)->setData('is_required',0)->setData('default_value','')->setData('used_in_forms', ['adminhtml_customer']) -> save();

				

		$customerSetup -> addAttribute(\Magento\Customer\Model\Customer::ENTITY,
			'is_account',
			[
			'label' => 'Is Account',
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

		$customerSetup -> getEavConfig() -> getAttribute('customer', 'is_account')->setData('is_user_defined',1)->setData('is_required',0)->setData('default_value','')->setData('used_in_forms', ['adminhtml_customer']) -> save();

				

		$customerSetup -> addAttribute(\Magento\Customer\Model\Customer::ENTITY,
			'is_active',
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

		$customerSetup -> getEavConfig() -> getAttribute('customer', 'is_active')->setData('is_user_defined',1)->setData('is_required',0)->setData('default_value','')->setData('used_in_forms', ['adminhtml_customer']) -> save();

				

		$customerSetup -> addAttribute(\Magento\Customer\Model\Customer::ENTITY,
			'tradin_name',
			[
			'label' => 'Tradin Name',
			'system' => 0,
			'position' => 999,
            'sort_order' =>999,
            'visible' =>  true,
			'note' => '',
				

                        'type' => 'varchar',
                        'input' => 'text',
			
			]
			);

		$customerSetup -> getEavConfig() -> getAttribute('customer', 'tradin_name')->setData('is_user_defined',1)->setData('is_required',0)->setData('default_value','')->setData('used_in_forms', ['adminhtml_customer']) -> save();

				
	} 

	public function installCustomerAddressAttributes($customerSetup) {
			
	} 

	public function getEavConfig() {
		return $this -> eavConfig;
	} 
} 