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
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $pageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory
    )
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Magenest ProductFeed'));
        return $page;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_ProductFeed::feed');
    }
}
