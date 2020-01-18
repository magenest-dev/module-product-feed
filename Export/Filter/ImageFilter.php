<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_productfeed extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_productfeed
 */

namespace Magenest\ProductFeed\Export\Filter;

use Magento\Framework\Filesystem;
use Magento\Framework\DataObject;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImageFilter
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem, $image = null)
    {
        $this->filesystem = $filesystem;
        $this->image = $image;
    }

    /**
     * Resize image
     *
     * @param string $input
     * @param int $width
     * @param int $height
     *
     * @return string
     */
    public function resize($input, $width = null, $height = null)
    {
        $media = $this->filesystem->getUri(DirectoryList::MEDIA);
        $paths = explode($media, $input);

        if (count($paths) == 2) {
            $image = $paths[1];

            return false;
        }

        return $input;
    }
}
