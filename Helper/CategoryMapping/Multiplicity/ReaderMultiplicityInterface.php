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
namespace Magenest\ProductFeed\Helper\CategoryMapping\Multiplicity;

use Magenest\ProductFeed\Helper\CategoryMapping\ReaderInterface;

interface ReaderMultiplicityInterface
{
    /**
     * @return $this
     */
    public function findAll();

    /**
     * @param ReaderInterface $item
     * @return $this
     */
    public function addItem(ReaderInterface $item);

    /**
     * @return array
     */
    public function getItems();
}
