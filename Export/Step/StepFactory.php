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

namespace Magenest\ProductFeed\Export\Step;

use Magento\Framework\ObjectManagerInterface;

class StepFactory
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create($className, array $data = [])
    {
        if (strpos($className, 'Magenest') === false) {
            $className = 'Magenest\ProductFeed\Export\Step\\' . $className;
        }

        $step = $this->objectManager->create($className, $data);

        return $step;
    }
}
