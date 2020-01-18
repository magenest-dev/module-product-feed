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

namespace Magenest\ProductFeed\Model;

use Magenest\ProductFeed\Helper\Io;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Feed Configuration Class
 */
class Config
{
    const STATUS_COMPLETED = 'completed';
    const STATUS_READY = 'ready';
    const STATUS_PROCESSING = 'processing';
    const STATUS_ERROR = 'error';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Io
     */
    protected $io;

    /**
     * Config constructor.
     *
     * @param Filesystem $filesystem
     * @param Io $io
     */
    public function __construct(
        Filesystem $filesystem,
        Io $io
    ) {
        $this->filesystem = $filesystem;
        $this->io = $io;
    }

    /**
     * Remove all folder and files from base feed folder
     *
     * @return bool
     */
    public function clearBasePath()
    {
        $this->io->rmdirRecursive($this->getBasePath());

        return true;
    }

    /**
     * Base path to feed directory (media/feed)
     *
     * @return string
     */
    public function getBasePath()
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'feed';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * @return string
     */
    public function getRootPath()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
    }

    /**
     * Path for store temporary files
     *
     * @return string
     */
    public function getTmpPath()
    {
        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . 'tmp';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Path to directory with templates
     *
     * @return string
     */
    public function getTemplatePath()
    {
        $path = dirname(dirname(__FILE__)) . '/Setup/data/template';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Path to directory with rules
     *
     * @return string
     */
    public function getRulePath()
    {
        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . 'rule';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Path to directory with Dynamic Attributes
     *
     * @return string
     */
    public function getDynamicAttributePath()
    {
        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . 'dynamic' . DIRECTORY_SEPARATOR . 'attribute';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Path to directory with Dynamic Categories
     *
     * @return string
     */
    public function getDynamicCategoryPath()
    {
        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . 'dynamic' . DIRECTORY_SEPARATOR . 'category';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Path to directory with Dynamic Variables
     *
     * @return string
     */
    public function getDynamicVariablePath()
    {
        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . 'dynamic' . DIRECTORY_SEPARATOR . 'variable';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Check and return maximum allowed script execution time
     *
     * @return int
     */
    public function getMaxAllowedTime()
    {

        $time = intval(ini_get('max_execution_time'));

        if ($time < 1 || $time > 30) {
            $time = 20;
        }

        return $time;
    }

    /**
     * Check and return maximum allowed memory
     *
     * @return int
     */
    public function getMaxAllowedMemory()
    {
        return 220 * 1024 * 1024;
    }
}
