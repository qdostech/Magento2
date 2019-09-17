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
         * Create table 'qdossync_sync'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('qdossync_sync')
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
		
		$installer->getConnection()->createTable($table);
		/*{{CedAddTable}}*/

        $installer->endSetup();


        /*START: Store Mapping*/
        $installerSM = $setup;

        $installerSM->startSetup();

        /**
         * Create table 'qdossync_storemapping'
         */
        $tableSM = $installerSM->getConnection()->newTable(
            $installerSM->getTable('qdossync_storemapping')
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'qdossync_storemapping'
        )
        ->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'store_id'
        )
        ->addColumn(
            'sync_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'sync_type'
        )
        /*{{CedAddTableColumn}}}*/
        
        
        ->setComment(
            'Qdos QdosSync qdossync_storemapping'
        );
        
        $installerSM->getConnection()->createTable($tableSM);
        /*{{CedAddTable}}*/

        $installerSM->endSetup();

        /*END: Store Mapping*/

        /*START: Sync Categories*/

        $installerSyncCat = $setup;

        $installerSyncCat->startSetup();

        /**
         * Create table 'qdossync_synccategories'
         */
        $tableSyncCat = $installerSyncCat->getConnection()->newTable(
            $installerSyncCat->getTable('qdossync_synccategories')
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'qdossync_synccategories'
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
        ->addColumn(
            'websites',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'websites'
        )
        ->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'status'
        )
        ->addColumn(
            'logdetails',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'logDetails'
        )
        /*{{CedAddTableColumn}}}*/
        
        
        ->setComment(
            'Qdos QdosSync qdossync_synccategories'
        );
        
        $installerSyncCat->getConnection()->createTable($tableSyncCat);
        /*{{CedAddTable}}*/

        $installerSyncCat->endSetup();

        /*END: Sync Categories*/

        $installer1 = $setup;

        $installer1->startSetup();

        /**
         * Create table 'qdossync_index'
         */
        $table1 = $installer1->getConnection()->newTable(
            $installer1->getTable('qdossync_index')
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
        
        $installer1->getConnection()->createTable($table1);
        /*{{CedAddTable}}*/

        $installer1->endSetup();


        /**
            * Create table for sync attribute
        **/

        $installerSyncAttribute = $setup;

        $installerSyncAttribute->startSetup();

        /**
         * Create table 'qdossync_syncattribute'
         */
        $tableSyncAttribute = $installerSyncAttribute->getConnection()->newTable(
            $installerSyncAttribute->getTable('qdossync_syncattribute')
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'qdossync_syncattribute'
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
        ->addColumn(
            'websites',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'websites'
        )
        ->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'status'
        )
        ->addColumn(
            'logdetails',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'logdetails'
        )
        /*{{CedAddTableColumn}}}*/
        
        
        ->setComment(
            'Qdos QdosSync qdossync_syncattribute'
        );
        
        $installerSyncAttribute->getConnection()->createTable($tableSyncAttribute);
        /*{{CedAddTable}}*/

        $installerSyncAttribute->endSetup();

    }
}
