<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */

namespace Neo\Productlocation\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
	
        $installer = $setup;

        $installer->startSetup();

		/**
         * Create table 'qdossync_sync'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('qdos_product_location')
        )
		->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'id'
        )
		->addColumn(
            'location_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '10',
            [],
            'location_id'
        )
       
		->addColumn(
            'location_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '100',
            [],
            'location_name'
        )
        ->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '10',
            [],
            'product_id'
        )
		/*{{CedAddTableColumn}}}*/
		
		
        ->setComment(
            'Storemapping'
        );
		
		$installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
