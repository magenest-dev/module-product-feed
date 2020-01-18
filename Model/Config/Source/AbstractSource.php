<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_SmartNet extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_SmartNet
 */

namespace Magenest\ProductFeed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

abstract class AbstractSource implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $allOptions = $this->getAllOptions();
        $result = [];
        foreach ($allOptions as $value => $label) {
            $result [] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $result;
    }

    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $key => $option) {
            if ($key == $value) {
                return $option;
            }
        }

        return "";
    }

    /**
     * @return array
     */
    abstract public static function getAllOptions();
}
