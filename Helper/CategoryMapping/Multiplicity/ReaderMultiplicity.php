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

abstract class ReaderMultiplicity implements ReaderMultiplicityInterface
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * {@inheritdoc}
     */
    public function addItem(ReaderInterface $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function findAll();
}
