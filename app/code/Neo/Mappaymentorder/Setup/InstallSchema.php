<?php
/**
 * Copyright © 2015 Qdos. All rights reserved.
 */

namespace Neo\Mappaymentorder\Setup;

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
            $installer->getTable('mappaymentorder')
        )
		->addColumn(
            'mappaymentorder_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'mappaymentorder_id'
        )
		->addColumn(
            'payment_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'payment_method'
        )
		->addColumn(
            'order_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'order_status'
        )
        ->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'status'
        )
        ->addColumn(
            'order_status_invoice',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'order_status_invoice'
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
            'Mappaymentorder'
        );
		
		$installer->getConnection()->createTable($table);

        $installer->endSetup();

        /*START: order_sync_status table*/

        $installer1 = $setup;

        $installer1->startSetup();

        /**
         * Create table 'order_sync_status'
         */
        $tableSync = $installer1->getConnection()->newTable(
            $installer1->getTable('order_sync_status')
        )
        ->addColumn(
            'order_sync_status_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'order_sync_status_id'
        )
        ->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '12',
            ['unique' =>true],
            'order_id'
        )
        ->addColumn(
            'sync_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '11',
            [],
            'sync_status'
        )
        ->addColumn(
            'cc_cid',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'cc_cid'
        )
        ->addColumn(
            'payment_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'payment_method'
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
        
                
        ->setComment(
            'order_sync_status'
        );
        
        $installer1->getConnection()->createTable($tableSync);

        $installer1->endSetup();


    }
}
