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

class FieldSeparate implements \Magento\Framework\Option\ArrayInterface
{
    const TAB = "\t";
    const COMMA = ",";
    const SEMI_COLON = ";";
    public function toOptionArray()
    {
        return[
            [
                'value'   =>  self::TAB,
                'label'   =>  __('Tab')
            ],
            [
                'value'   =>  self::COMMA,
                'label'   =>  __('Comma')
            ],
            [
                'value'   =>  self::SEMI_COLON,
                'label'   =>  __('Semi-Colon')
            ]
        ];
    }
}
