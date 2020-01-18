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

namespace Magenest\ProductFeed\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Zend_Validate_Callback;

class ProductFeed extends AbstractDb
{
    const MAGENEST_PRODUCT_FEED_TABLE = "magenest_product_feed";

    public function getValidationRulesBeforeSave()
    {
        $fileNameIdentity = new Zend_Validate_Callback([$this, 'isFileNameUnique']);
        $fileNameIdentity->setMessage(
            __('A user with the same user name or email already exists.'),
            Zend_Validate_Callback::INVALID_VALUE
        );
        return $fileNameIdentity;
    }

    public function isFileNameUnique(AbstractModel $feed)
    {
        return !$this->fileNameExist($feed);
    }

    public function fileNameExist(AbstractModel $feed)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $binds = [
            'filename' => $feed->getData('filename'),
            'id' => $feed->getId()
        ];

        $select->from(
            $this->getMainTable(),
            $this->getIdFieldName()
        )->where(
            '(filename = :filename)'
        )->where(
            'id <> :id'
        );
        return $connection->fetchOne($select, $binds);
    }

    protected function _construct()
    {
        $this->_init(self::MAGENEST_PRODUCT_FEED_TABLE, 'id');
    }
}
