<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */

namespace Neo\Storemapping\Setup;

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
            $installer->getTable('storemapping')
        )
		->addColumn(
            'storemapping_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'storemapping_id'
        )
		->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '10',
            [],
            'store_id'
        )
		->addColumn(
            'sync_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'sync_type'
        )
		->addColumn(
            'created_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'created_time'
        )
		->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'update_time'
        )
		/*{{CedAddTableColumn}}}*/
		
		
        ->setComment(
            'Storemapping'
        );
		
		$installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
