<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_magento233 extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_magento233
 */
namespace Magenest\ProductFeed\Model\Config\Source;

class FilterStatus implements \Magento\Framework\Option\ArrayInterface
{
    const DISABLE = "0";
    const ENABLE = "1";
    public function toOptionArray()
    {
        return[
            [
                'value'   =>  self::DISABLE,
                'label'   =>  __('Disable')
            ],
            [
                'value'   =>  self::ENABLE,
                'label'   =>  __('Enable')
            ]
        ];
    }
}