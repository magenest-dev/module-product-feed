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

namespace Magenest\ProductFeed\Model\Config\Source;

class StatusSendMail implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return[
            [
                'value'   =>  1,
                'label'   =>  __('Generated successfully')
            ],
            [
                'value'   =>  0,
                'label'   =>  __('Generated error')
            ],
        ];
    }
}
