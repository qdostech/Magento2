<?php

namespace Qdos\QdosSync\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        
        if (version_compare($context->getVersion(), '1.0.1', '<')){
            $table = $setup->getConnection()->newTable(
            $setup->getTable('qdos_activity_log')
            )->addColumn(
                'log_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Log Id'
            )->addColumn(
                'activity_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,100,[],
                'activity_type'
            )->addColumn(
                'start_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Start Time'
            )->addColumn(
                'end_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'End Time'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,null,[],
                'Status'
            )->addColumn(
                'ip_address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,20,[],
                'ip address'
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,[],
                [],
                'description'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                [],[],
                'store_id'
            )->addColumn(
                'product_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,[],
                [],
                'product_ids'
            )->addColumn(
                'msg',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,[],
                [],
                'msg'
            )->setComment(
                'qdos_activity_log'
            );
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')){

            $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_entity'),
                'sync_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default'  => null,
                    'comment' => 'Sync Status'
                ]
            );
        
            $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_entity'),
                'last_sync',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'nullable' => true,
                    'default'  => null,
                    'comment' => 'Last Sync'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_entity'),
                'last_log_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 20,
                    'nullable' => true,
                    'default'  => null,
                    'comment' => 'Last Log Id'
                ]
            );
        }

        $setup->endSetup();
    }
}