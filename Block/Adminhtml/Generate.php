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

namespace Magenest\ProductFeed\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Generate extends Template
{
    protected $productFactory;

    public function __construct(
        CollectionFactory $productFactory,
        Template\Context $context,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    public function getTotalRecord()
    {
        $collection = $this->productFactory->create()->getSize();
    }
}
