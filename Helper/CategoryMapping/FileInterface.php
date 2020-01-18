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

interface FileInterface extends ReaderInterface
{
    /**
     * @param string $file
     * @return $this
     */
    public function setFile($file);

    /**
     * @return string
     */
    public function getFile();
}
