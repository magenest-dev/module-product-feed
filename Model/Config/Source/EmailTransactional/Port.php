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

namespace Magenest\ProductFeed\Model\Config\Source\EmailTransactional;

class Port implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '25', 'label' => '25'],
            ['value' => '2525', 'label' => '2525'],
            ['value' => '587', 'label' => '587'],
        ];
    }
}
