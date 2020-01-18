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

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\Type\Time;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    private $state;

    /**
     * UpgradeSchema constructor.
     *
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\App\State $state
    ) {
        $this->state = $state;
    }

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $this->state->emulateAreaCode('global', function ($setup) {
                $this->addGenerateProcessColumns($setup);
                $this->changeUpdateAtColumn($setup);
                $this->changeFileNameUnique($setup);
                $this->changeNameOfLogsColumn($setup);

                $this->changeFileNameOfLogsTable($setup);

                $this->changeNameColumnIdOfLogs($setup);
            }, [$setup]);
        }
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function changeUpdateAtColumn($setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable(\Magenest\ProductFeed\Model\ResourceModel\ProductFeed::MAGENEST_PRODUCT_FEED_TABLE),
            'updated_at',
            'updated_at',
            [
                'type'=> Table::TYPE_TIMESTAMP,
                'size' => null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_UPDATE]
            ]
        );
    }
    private function changeNameOfLogsColumn($setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable(\Magenest\ProductFeed\Model\ResourceModel\Logs::MAGENEST_PRODUCT_FEED_LOGS_TABLE),
            'feed_name',
            'filename',
            [
                'type'=> Table::TYPE_TEXT,
                'size' => 50
            ]
        );
    }

    private function changeFileNameOfLogsTable($setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable(\Magenest\ProductFeed\Model\ResourceModel\Logs::MAGENEST_PRODUCT_FEED_LOGS_TABLE),
            'filename',
            'file_name',
            [
                'type'=> Table::TYPE_TEXT,
                'size' => 50
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function changeNameColumnIdOfLogs($setup)
    {

        $setup->getConnection()->changeColumn(
            $setup->getTable(\Magenest\ProductFeed\Model\ResourceModel\Logs::MAGENEST_PRODUCT_FEED_LOGS_TABLE),
            'id',
            'logs_id',
            [
                'type'=> Table::TYPE_INTEGER,
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        );
    }

    private function changeFileNameUnique($setup)
    {
        $setup->getConnection()->addIndex(
            $setup->getTable(\Magenest\ProductFeed\Model\ResourceModel\ProductFeed::MAGENEST_PRODUCT_FEED_TABLE),
            $setup->getConnection()->getIndexName(
                $setup->getTable(\Magenest\ProductFeed\Model\ResourceModel\ProductFeed::MAGENEST_PRODUCT_FEED_TABLE),
                ['filename'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['filename'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
    private function addGenerateProcessColumns($setup)
    {
        $feedTable = $setup->getTable(\Magenest\ProductFeed\Model\ResourceModel\ProductFeed::MAGENEST_PRODUCT_FEED_TABLE);
        $columns = [
            "header_generated" => [
                'type' => Table::TYPE_BOOLEAN,
                'length' => null,
                'nullable' => true,
                'comment' => "Is Header Generated"
            ],
            "footer_generated" => [
                'type' => Table::TYPE_BOOLEAN,
                'length' => null,
                'nullable' => true,
                'comment' => "Is Footer Generated"
            ],
            "last_product_id" => [
                'type' => Table::TYPE_TEXT,
                'length' => 127,
                'nullable' => true,
                'comment' => "Last Generated Product ID"
            ],
            "is_generation_processing" => [
                'type' => Table::TYPE_BOOLEAN,
                'length' => null,
                'nullable' => true,
                'comment' => "Is Generation Processing"
            ],
            "matching_product_id"=> [
                'type' => Table::TYPE_TEXT,
                'length' => 16777216,
                'nullable' => true,
                'comment' => "Matching Product Id"
            ]
        ];
        foreach ($columns as $columnName => $definition) {
            $setup->getConnection()->addColumn($feedTable, $columnName, $definition);
        }
    }
}
