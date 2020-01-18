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

use Magento\Framework\App\Filesystem\DirectoryList;

class FileReaderMultiplicity extends ReaderMultiplicity
{
    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $mappingPaths = $this->getMappingPaths();

        foreach ($mappingPaths as $mappingPath) {
            foreach (glob($mappingPath . "/*.txt") as $filename) {
                /** @var \Magenest\ProductFeed\Helper\CategoryMapping\FileInterface $fileReader */
                $fileReader = $this->getReader();
                $this->addItem($fileReader->setFile($filename));
            }
        }

        return $this;
    }

    /**
     * @return \Magenest\ProductFeed\Helper\CategoryMapping\FileInterface
     */
    protected function getReader()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        return $om->create('Magenest\ProductFeed\Helper\CategoryMapping\FileReader');
    }

    /**
     * @return array
     */
    protected function getMappingPaths()
    {
        $paths = [];

        $om = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Framework\Module\Dir\Reader $reader */
        $directoryReader = $om->get('Magento\Framework\Module\Dir\Reader');
        $paths[] = realpath($directoryReader->getModuleDir('etc', 'Magenest_ProductFeed') . '/../Setup/data/mapping/');

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $om->get('Magento\Framework\Filesystem');

        $paths[] = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'feed/mapping/';

        return $paths;
    }
}
