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

namespace Magenest\ProductFeed\Helper\CategoryMapping;

interface ReaderInterface
{
    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit);

    /**
     * @return int
     */
    public function getLimit();

    /**
     * @param string $search
     * @return array
     */
    public function getRows($search);
}
