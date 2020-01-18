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

use Exception;
use Magenest\ProductFeed\Controller\Adminhtml\AbstractFeed;
use Magenest\ProductFeed\Model\ProductFeed;

class Delete extends AbstractFeed
{
    const ADMIN_RESOURCE = "Magenest_ProductFeed::delete";
    public function execute()
    {
        /** @var ProductFeed $feed */
        $feed = $this->initFeed();
        if ($feed->getId()) {
            try {
                $feed->delete();
                $this->messageManager->addSuccessMessage(__('The Feed has been deleted.'));
                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->_redirect('*/*/');
            }
        }
    }   
}
