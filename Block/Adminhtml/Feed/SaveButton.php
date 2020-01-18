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

namespace Magenest\ProductFeed\Block\Adminhtml\Feed;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    protected $request;
    public function __construct(
        Context $context,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $id = $this->request->getParam('feed_id');
        if ($id) {
            return [
                'label' => __('Save Feed'),
                'class' => 'save primary',
                'sort_order' => 90,
            ];
        }
    }
}
