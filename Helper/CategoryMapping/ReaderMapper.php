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

use \Magenest\ProductFeed\Helper\CategoryMapping\Multiplicity\ReaderMultiplicityInterface;

class ReaderMapper
{
    /**
     * @var array
     */
    protected $multiplicityArray = [];

    /**
     * @var string
     */
    protected $mappingDelimiter = ' > ';

    /**
     * @param string $search
     *
     * @return array
     */
    public function getData($search)
    {
        $data = [];
        $result = [];
        /** @var ReaderMultiplicityInterface $multiplicity */
        foreach ($this->multiplicityArray as $multiplicity) {
            $items = $multiplicity->getItems();
            /** @var ReaderInterface $item */
            foreach ($items as $item) {
                $item->setMappingDelimiter($this->mappingDelimiter);
                $data = array_merge($data, $item->getRows($search));
            }
        }

        foreach ($data as $path => $row) {
            $result[] = [
                'file'  => $row,
                'path'  => $path,
                'label' => $path,
                'id'    => $path,
            ];
        }

        return $result;
    }

    /**
     * @param ReaderMultiplicityInterface $multiplicity
     * @return $this
     */
    public function addMultiplicity($multiplicity)
    {
        $this->multiplicityArray[] = $multiplicity;

        return $this;
    }
}
