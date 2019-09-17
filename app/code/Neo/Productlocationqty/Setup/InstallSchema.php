<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */

namespace Neo\Productlocationqty\Setup;

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
            $installer->getTable('qdos_quote_product_qty')
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
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '10',
            [],
            'quote_id'
        )

        ->addColumn(
            'quantity',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '10',
            [],
            'quantity'
        )

        ->addColumn(
            'qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '10',
            [],
            'qty'
        )
    
        ->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '10',
            [],
            'product_id'
        )

        ->addColumn(
            'sku',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'sku'
        )
		/*{{CedAddTableColumn}}}*/
		
		
        ->setComment(
            'Storemapping'
        );
		
		$installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
