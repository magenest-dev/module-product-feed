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

namespace Magenest\ProductFeed\Model;

use Magento\Framework\Model\AbstractModel;

class Logs extends AbstractModel
{

    protected function _construct()
    {
        $this->_init('Magenest\ProductFeed\Model\ResourceModel\Logs');
    }
}
