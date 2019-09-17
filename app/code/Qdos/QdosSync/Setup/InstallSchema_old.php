<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */

namespace Qdos\QdosSync\Setup;

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
         * Create table 'qdossync_index'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('qdossync_index')
        )
		->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'qdossync_index'
        )
		->addColumn(
            'log',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'log'
        )
		->addColumn(
            'start_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'start_date'
        )
		->addColumn(
            'end_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'end_date'
        )
		/*{{CedAddTableColumn}}}*/
		
		
        ->setComment(
            'Qdos QdosSync qdossync_index'
        );
		
		$installer->getConnection()->createTable($table);
		/*{{CedAddTable}}*/

        $installer->endSetup();








        $installer1 = $setup;

        $installer1->startSetup();

        /**
         * Create table 'qdossync_sync'
         */
        $table1 = $installer1->getConnection()->newTable(
            $installer1->getTable('qdossync_sync')
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'qdossync_sync'
        )
        ->addColumn(
            'activity',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'activity'
        )
        ->addColumn(
            'fromip',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'fromIP'
        )
        ->addColumn(
            'start',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'start'
        )
        ->addColumn(
            'finish',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'finish'
        )
        /*{{CedAddTableColumn}}}*/
        
        
        ->setComment(
            'Qdos QdosSync qdossync_sync'
        );
        
        $installer1->getConnection()->createTable($table1);
        /*{{CedAddTable}}*/

        $installer1->endSetup();

    }
}
