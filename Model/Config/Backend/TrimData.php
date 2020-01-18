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

namespace Magenest\ProductFeed\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

class TrimData extends Value
{
    public function beforeSave()
    {
        $value = $this->getValue();
        $trimmedValue = trim($value);
        $this->setValue($trimmedValue);

        return parent::beforeSave();
    }
}
