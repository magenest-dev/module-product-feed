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

namespace Magenest\ProductFeed\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class NewAction extends Action
{
    const ADMIN_RESOURCE = 'Magenest_ProductFeed::addedit';
    public function execute()
    {
        $this->_forward('edit');
    }
}
