<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_magento extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_magento
 */
namespace Magenest\ProductFeed\Model\Config\Source;

class IncludeFieldHeader implements \Magento\Framework\Option\ArrayInterface
{
    const YES = 0;
    const NO = 1;
    public function toOptionArray()
    {
        return[
            [
                'value'   =>  self::YES,
                'label'   =>  __('Yes')
            ],
            [
                'value'   =>  self::NO,
                'label'   =>  __("No")
            ]
        ];
    }
}
