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

namespace Magenest\ProductFeed\Block\Adminhtml\Menu\Edit\Tab;

use Magenest\ProductFeed\Helper\Data;
use Magenest\ProductFeed\Model\Config\Source\FileType;
use Magenest\ProductFeed\Model\ProductFeed;
use Magenest\ProductFeed\Model\ProductFeedFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\System\Store;

class General extends Generic implements TabInterface
{
    /** @var ReadFactory */
    public $readFactory;

    /**
     * @var Store
     */
    protected $systemStore;

    protected $productFeedFactory;

    protected $_storeManager;

    /** @var Data */
    protected $helperData;

    /** @var Filesystem */
    protected $_filesystem;

    protected $filetype;

    /** @var DirectoryList */
    protected $_dictorylist;

    /** @var FileFactory */
    protected $_filefactory;

    public function __construct(
        ProductFeedFactory $productFeedFactory,
        Data $helperData,
        ReadFactory $readFactory,
        Filesystem $_filesystem,
        FileType $fileType,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        StoreManagerInterface $_storeManager,
        Store $systemStore,
        DirectoryList $_dictorylist,
        FileFactory $_filefactory,
        array $data = []
    ) {
        $this->productFeedFactory = $productFeedFactory;
        $this->filetype = $fileType;
        $this->helperData = $helperData;
        $this->_filesystem = $_filesystem;
        $this->readFactory = $readFactory;
        $this->systemStore = $systemStore;
        $this->_storeManager = $_storeManager;
        $this->_dictorylist = $_dictorylist;
        $this->_filefactory = $_filefactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        // TODO: Implement getTabLabel() method.
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        // TODO: Implement getTabTitle() method.
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return true;
    }

    protected function _prepareLayout()
    {
        /** @var ProductFeed $model */
        $model = $this->_coreRegistry->registry('information');
        $idFeed = $this->_request->getParam('feed_id');
        $urlFile = $this->helperData->getUrlFile($model->getFilename());
        $setUrlFile = $model->setData('file_url', $urlFile);

        $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::PUB);
        $path = '/productfeed/' . $model->getFilename();
        $isFile = $filePath->isFile($path);
        $form = $this->_formFactory->create();
        $id = $this->getRequest()->getParam('feed_id');
        $feed = $this->productFeedFactory->create()->load($id);
        $type = $feed->getTypeTemplate();
        $timeCreate = $model->getUpdatedAt();
        $fieldset = $form->addFieldset(
            'edit_form',
            [
                'legend' => __('Information')
            ]
        );

        if ($model->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                [
                    'name' => 'id',
                    'value' => $model->getId()
                ]
            );
        }
        $fieldset->addField(
            'type_template',
            'select',
            [
                'label' => __('Template'),
                'name' => 'type_template',
                'values' => [
                    ['label' => __('Facebook (CSV)'), 'value' => 'facebook'],
                    ['label' => __('Google Shopping (XML)'), 'value' => 'google']
                ],
                'required' => true,
                'disabled' => $model->getId()
            ]
        );
        if ($id) {
            $fieldset->addField(
                'feed_name',
                'text',
                [
                    'label' => __('Name'),
                    'name' => 'feed_name',
                    'required' => $model->getId(),
                ]
            );

            $fieldset->addField(
                'filetype',
                'select',
                [
                    'label' => __('File Type'),
                    'name' => 'filetype',
                    'values' => $this->filetype->toOptionArray(),
                    'readonly' => true,
                    'required' => false,
                ]
            );

            $fieldset->addField(
                'status',
                'select',
                [
                    'label' => __('Cron Status'),
                    'name' => 'status',
                    'values' => [
                        ['label' => __('Disable'), 'value' => '0'],
                        ['label' => __('Enable'), 'value' => '1']
                    ],
                    'required' => true,
                ]
            );

            $fieldset->addField(
                'store_id',
                'select',
                [
                    'label' => __('Store View'),
                    'name' => 'store_id',
                    'values' => $this->systemStore->getStoreValuesForForm(false, true),
                    'required' => true,
                ]
            );

            $fieldset->addField(
                'filename',
                'text',
                [
                    'label' => __('File Name'),
                    'name' => 'filename',
                    'class' => 'alphanumeric',
                    'maxlength' => 25,
                    'note' => __('Generated file is placed in directory pub/productfeed.').'<br>'.__('File name is less than 25 characters'),
                    'required' => true
                ]
            );
        }

        if (isset($idFeed) && $isFile == true && isset($timeCreate)) {
            $fieldset->addField(
                'file_url',
                'link',
                [
                    'name' => 'file_url',
                    'href' => $this->getUrl('feed/index/download', ['filename' => $model->getFilename()]),
                    'label' => __('Generated File URL'),
                    'title' => __('Generated File URL'),
                    'value' => $urlFile,
                    'class' => "control-value"
                ]
            );
            $fieldset->addField(
                'updated_at',
                'label',
                [
                    'name' => 'updated_at',
                    'label' => __('Generated On'),
                    'title' => __('Generated On'),
                    'value' => $this->helperData->convertTime($model->getUpdatedAt())
                ]
            );
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareLayout();
    }
}
