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

namespace Magenest\ProductFeed\Block\Adminhtml\Menu\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('feed_index_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Magenest Product Feed'));
    }

    protected function _beforeToHtml()
    {
        $id = $this->getRequest()->getParam('feed_id');

        $this->addTab(
            'general',
            [
            'label' => __('General Information'),
            'title' => __('General Information'),
            'content' => $this->getLayout()->createBlock(
                'Magenest\ProductFeed\Block\Adminhtml\Menu\Edit\Tab\General'
            )->toHtml(),
            'active' => true
            ]
        );

        if ($id) {
            $this->addTab(
                'templates',
                [
                'label' => __('Feed Templates'),
                'title' => __('Feed Templates'),
                'content' => $this->getLayout()->createBlock(
                    'Magenest\ProductFeed\Block\Adminhtml\Menu\Edit\Tab\Template'
                )->toHtml()
                ]
            );
            $this->addTab(
                'rule',
                [
                'label' => __(' Filter'),
                'title' => __('Filter'),
                'content' => $this->getLayout()->createBlock(
                    'Magenest\ProductFeed\Block\Adminhtml\Menu\Edit\Tab\Conditions'
                )->toHtml()
                ]
            );
            $this->addTab(
                'mapping',
                [
                'label' => __('Mapping Category'),
                'title' => __('Mapping Category'),
                'content' => $this->getLayout()->createBlock('Magenest\ProductFeed\Block\Adminhtml\Menu\Edit\Tab\Mapping')->toHtml(),
                ]
            );

        }

        return parent::_beforeToHtml();
    }
}
