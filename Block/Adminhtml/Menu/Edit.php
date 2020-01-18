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

namespace Magenest\ProductFeed\Block\Adminhtml\Menu;

use Magenest\ProductFeed\Model\ProductFeed;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Magenest\ProductFeed\Block\Adminhtml\Menu
 */
class Edit extends Container
{
    /**
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl(
            '*/*/delete',
            [
                '_current' => true,
                'id' => $this->getRequest()->getParam('region_id')
            ]
        );
    }

    protected function _construct()
    {
        /** @var ProductFeed $model */
        $model = $this->_coreRegistry->registry('information');
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Magenest_ProductFeed'; //name Vendor_Module
        $this->_controller = 'adminhtml_menu'; //name folder chứa Edit.php
        parent::_construct();

        $this->buttonList->add(
            'save_and_continue',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),

                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );

        if ($model->getData('filename')) {
            $this->buttonList->add('generate', [
                'label' => __('Generate'),
                'class' => 'generate',
                'data_attribute' => [
                    'mage-init' => [
                        'generate' => [
                            'generateFeedUrl' => $this->getGenerateUrl()
                        ],
                    ]
                ],
            ], -90);
        }
        $this->buttonList->remove('delete');
        if (empty($model->getId())) {
            $this->buttonList->remove('save');
        }
    }

    protected function getGenerateUrl()
    {
        $feed = $this->_coreRegistry->registry('information');

        return $this->getUrl('*/*/generate', ['id' => $feed->getId()]);
    }

    /**
     * @param $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            [
                '_current' => true,
                'back' => 'edit',
                'active_tab' => ''
            ]
        );
    }
}
