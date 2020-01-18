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

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class   Logs extends AbstractDb
{
    const MAGENEST_PRODUCT_FEED_LOGS_TABLE = "magenest_product_feed_logs";
    protected function _construct()
    {
        $this->_init('magenest_product_feed_logs', 'id');
    }
}
