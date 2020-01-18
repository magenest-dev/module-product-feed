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

class FieldAroundBy implements \Magento\Framework\Option\ArrayInterface
{
    const NONE = '';
    const CHARACTER1 = '"'; // "
    const CHARACTER2 = "'"; // '
    public function toOptionArray()
    {
        return[
            [
                'value'   =>  self::NONE,
                'label'   =>  __('None')
            ],
            [
                'value'   =>  self::CHARACTER1,
                'label'   =>  __('"')
            ],
            [
                'value'   =>  self::CHARACTER2,
                'label'   =>  __("'")
            ]
        ];
    }
}
