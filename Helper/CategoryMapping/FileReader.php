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

class FileReader implements FileInterface
{
    /**
     * @var int
     */
    protected $limit = 100;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $mappingDelimiter = ' > ';

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $search
     * @return array
     */
    public function getRows($search)
    {
        $result = [];
        $file = $this->getFile();
        if (isset($file)) {
            $handle = fopen($file, "r");
            $i = 0;
            while (($buffer = fgets($handle, 4096)) !== false) {
                if (($this->getLimit() && $i >= $this->getLimit())) {
                    break;
                }
                if (stripos($buffer, $search) !== false) {
                    $categories = explode($this->getMappingDelimiter(), trim($buffer));
                    $buffer = implode($this->getMappingDelimiter(), $categories);
                    $result[$buffer] = $this->getFileName();
                    $i++;
                }
            }
            fclose($handle);
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getMappingDelimiter()
    {
        return $this->mappingDelimiter;
    }

    /**
     * @param string $delimiter
     * @return $this
     */
    public function setMappingDelimiter($delimiter)
    {
        $this->mappingDelimiter = $delimiter;

        return $this;
    }

    /**
     * @return string
     */
    protected function getFileName()
    {
        return basename($this->getFile());
    }
}
