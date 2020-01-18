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

namespace Magenest\ProductFeed\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $statusValue = $item['status'];
                $statusClass = $statusValue % 2;
                switch ($statusClass) {
                    case 0:
                        $class = 'grid-severity-';
                        $label = 'critical';
                        $name = 'DISABLE';
                        break;
                    default:
                        $class = 'grid-severity-';
                        $label = 'notice';
                        $name = 'ENABLE';
                        break;
                }
                $item[$this->getData('name')] = "<span class=" . $class . $label . " >$name</span>";
            }
        }
        return $dataSource;
    }
}
