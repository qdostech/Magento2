<?php
namespace Qdos\Sync\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
       
        if (version_compare($context->getVersion(), '3.1.39', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('qdos_activity_log'),
                'log_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 11,
                    'nullable' => false,
                    'comment' => 'Log id',
                    'auto_increment' => true,
                    'unsigned' => true,   
                    'primary'  => true ,
                    'identity' => true             
                ]
            );
          $installer->getConnection()->addColumn(
                $installer->getTable('qdos_activity_log'),
                'activity_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => false,
                    'default' => 'product',
                    'comment' => 'Activity Type'                    
                ]
            );
          
          $installer->getConnection()->addColumn(
                $installer->getTable('qdos_activity_log'),
                'start_time',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'nullable' => true,
                    'default' => NULL,
                    'comment' => 'Start Time'                    
                ]
            );
          $installer->getConnection()->addColumn(
                $installer->getTable('qdos_activity_log'),
                'end_time',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'nullable' => true,
                    'default' => NULL,
                    'comment' => 'End Time'                    
                ]
            );
           $installer->getConnection()->addColumn(
                $installer->getTable('qdos_activity_log'),
                'status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' =>11,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Status'                    
                ]
          );
          $installer->getConnection()->addColumn(
                $installer->getTable('qdos_activity_log'),
                'ip_address',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' =>20,
                    'nullable' => true,
                    'default' => NULL,
                    'comment' => 'Ip Address'                    
                ]
          );
          $installer->getConnection()->addColumn(
                $installer->getTable('qdos_activity_log'),
                'description',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' =>20,
                    'nullable' => true,
                    'default' => NULL,
                    'comment' => 'Description'                    
                ]
          );
          $installer->getConnection()->addColumn(
                $installer->getTable('qdos_activity_log'),
                'from_batch',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' =>4,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'From Batch'                    
                ]
          );
          $installer->getConnection()->addColumn(
                $installer->getTable('qdos_activity_log'),
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' =>20,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Store Id'                    
                ]
          );
          $installer->getConnection()->addColumn(
                $installer->getTable('qdos_activity_log'),
                'product_ids',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => NULL,
                    'comment' => 'Product Ids'                    
                ]
          );
          $installer->getConnection()->addColumn(
                $installer->getTable('qdos_activity_log'),
                'msg',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => NULL,
                    'comment' => 'Msg'                    
                ]
          );
          
            
        }
        $installer->endSetup();
    }
}