<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_ProductFeed extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_ProductFeed
 */

namespace Magenest\ProductFeed\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        $table_template = $connection->newTable($setup->getTable('magenest_product_feed_template'));
        $table_template
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'ID Default Template'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                50,
                [],
                'Name Default Template'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                50,
                [],
                'Title Default Template'
            )
            ->addColumn(
                'type',
                Table::TYPE_TEXT,
                10,
                [],
                'Type Default Template'
            )->addColumn(
                'content',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Content Default Template'
            )
            ->setComment('Magenest Default Template');
        $setup->getConnection()->createTable($table_template);

        $table_productfeed = $connection->newTable($setup->getTable('magenest_product_feed'));
        $table_productfeed
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'ID'
            )
            ->addColumn(
                'feed_name',
                Table::TYPE_TEXT,
                50,
                [],
                'Feed Name'
            )
            ->addColumn(
                'filename',
                Table::TYPE_TEXT,
                50,
                [],
                'File Name'
            )
            ->addColumn(
                'filetype',
                Table::TYPE_TEXT,
                50,
                [],
                'File Type'
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                2,
                [],
                'Status Feed'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_TEXT,
                50,
                [],
                'Store ID'
            )->addColumn(
                'type_template',
                Table::TYPE_TEXT,
                50,
                [],
                'Type Template'
            )->addColumn(
                'name_template',
                Table::TYPE_TEXT,
                50,
                [],
                'Name Template'
            )->addColumn(
                'template_content',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Template Content'
            )->addColumn(
                'attribute_template',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Attribute Template'
            )->addColumn(
                'conditions_serialized',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Conditions'
            )->addColumn(
                'template_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true,],
                'Template ID'
            )
            ->addColumn(
                'mapping_json',
                Table::TYPE_TEXT,
                '2M',
                ['unsigned' => true,],
                'Mapping Data Json'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Create At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Update At'
            )
            ->setComment('Magenest Product Feed');
        $setup->getConnection()->createTable($table_productfeed);


        $table_logs = $connection->newTable($setup->getTable('magenest_product_feed_logs'));
        $table_logs
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'ID'
            )
            ->addColumn(
                'feed_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true,],
                'Feed ID'
            )
            ->addColumn(
                'feed_name',
                Table::TYPE_TEXT,
                50,
                [],
                'Feed Name'
            )
            ->addColumn(
                'feed_status',
                Table::TYPE_INTEGER,
                2,
                [],
                'Status Feed'
            )
            ->addColumn(
                'create_at',
                Table::TYPE_TIMESTAMP,
                null,
                [],
                'Create At'
            )
            ->addForeignKey(
                $setup->getFkName($setup->getTable("magenest_product_feed_logs"), 'feed_id',$setup->getTable("magenest_product_feed"),'id'),
                'feed_id',
                $setup->getTable('magenest_product_feed'),
                'id',
                Table::ACTION_CASCADE
            )
            ->setComment('Magenest Product Feed Logs');
        $setup->getConnection()->createTable($table_logs);
        $setup->endSetup();
    }
}
