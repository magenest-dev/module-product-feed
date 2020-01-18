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

use Magenest\ProductFeed\Controller\Adminhtml\AbstractFeed;
use Magenest\ProductFeed\Model\ProductFeedFactory;
use Magenest\ProductFeed\Model\ResourceModel\ProductFeed;
use Magenest\ProductFeed\Model\TemplatesFactory;
use Magento\Backend\App\Action\Context;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

class Download extends AbstractFeed
{
    /** @var DirectoryList */
    protected $_dictorylist;
    /** @var FileFactory */
    protected $_filefactory;

    public function __construct(
        ProductFeedFactory $modelFactory,
        TemplatesFactory $templateFactory,
        Registry $registry,
        Context $context,
        RuleFactory $ruleFactory,
        RedirectFactory $resultRedirectFactory,
        ProductFeed $resourceFeed,
        Json $jsonHelper,
        DirectoryList $_dictorylist,
        FileFactory $_filefactory
    ) {
        $this->_dictorylist = $_dictorylist;
        $this->_filefactory = $_filefactory;
        parent::__construct($modelFactory, $templateFactory, $registry, $context, $ruleFactory, $resultRedirectFactory, $resourceFeed, $jsonHelper);
    }

    public function execute()
    {
        $feed = $this->initFeed();
        $fileName = $this->getRequest()->getParam('filename');
        $file = $this->_dictorylist->getPath("pub") . "/productfeed/" . $fileName;
        return $this->_filefactory->create(
            $fileName,
            [
                'type' => "filename",
                'value' => $file,
                'rm' => false,
            ],
            DirectoryList::PUB
        );
    }
}
