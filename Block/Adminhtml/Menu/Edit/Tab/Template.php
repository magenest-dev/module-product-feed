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

use Magenest\ProductFeed\Block\Adminhtml\Template\Edit\Tab\Renderer\TemplateContent;
use Magenest\ProductFeed\Model\Config\Source\FieldAroundBy;
use Magenest\ProductFeed\Model\Config\Source\FieldSeparate;
use Magenest\ProductFeed\Model\Config\Source\FileType;
use Magenest\ProductFeed\Model\Config\Source\IncludeFieldHeader;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

class Template extends Generic implements TabInterface
{
    /**
     * @var Yesno
     */
    protected $yesNo;

    /**
     * @var FileType
     */
    protected $fileType;

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    protected $separateType;

    protected $filedAroundBy;

    protected $includeFieldHeader;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        FieldFactory $fieldFactory,
        FieldSeparate $separateType,
        FieldAroundBy $fieldAroundBy,
        IncludeFieldHeader $includeFieldHeader,
        Yesno $yesNo,
        FileType $fileType,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->fieldFactory = $fieldFactory;
        $this->fileType = $fileType;
        $this->separateType = $separateType;
        $this->filedAroundBy = $fieldAroundBy;
        $this->includeFieldHeader = $includeFieldHeader;
        $this->yesNo = $yesNo;
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Template');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        $formHtml = parent::getFormHtml();
        $childHtml = $this->getChildHtml();

        return $formHtml . $childHtml;
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('information');
        $template = $model->getTypeTemplate();
        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('feed_');
        $form->setFieldNameSuffix('feed');

        $fieldset = $form->addFieldset('template_fieldset', [
            'legend' => __('Templates'),
            'class' => 'fieldset-wide'
        ]);

        if ($template == 'google') {
            $fieldset->addField('titlefeed', 'text', [
                'name' => 'titlefeed',
                'label' => __('Title Feed'),
                'title' => __('Title Feed'),
                'required' => true ? ($model->getId()) : false,
            ]);
            $setTitleFeed = $model->setData('titlefeed', json_decode($model->getAttributeTemplate(), true)['titlefeed']);

            $fieldset->addField('description', 'textarea', [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => true ? ($model->getId()) : false,
            ]);
            $setDescription = $model->setData('description', json_decode($model->getAttributeTemplate(), true)['description']);


        } elseif ($template == 'facebook') {
            $fieldset->addField('fieldseparate', 'select', [
                'name' => 'fieldseparate',
                'label' => __('Field Separate'),
                'title' => __('Field Separate'),
                'values' => $this->separateType->toOptionArray()
            ]);

            if (isset(json_decode($model->getAttributeTemplate(), true)['fieldseparate'])) {
                $setFieldSeparate = $model->setData('fieldseparate', json_decode($model->getAttributeTemplate(), true)['fieldseparate']);
            }

            $fieldset->addField('fieldaroundby', 'select', [
                'name' => 'fieldaroundby',
                'label' => __('Field Around By'),
                'title' => __('Field Around By'),
                'values' => $this->filedAroundBy->toOptionArray()
            ]);

            if (isset(json_decode($model->getAttributeTemplate(), true)['fieldaroundby'])) {
                $setFieldSeparate = $model->setData('fieldaroundby', json_decode($model->getAttributeTemplate(), true)['fieldaroundby']);
            }

            $fieldset->addField('includefieldheader', 'select', [
                'name' => 'includefieldheader',
                'label' => __('Include Field Header'),
                'title' => __('Include Field Header'),
                'values' => $this->includeFieldHeader->toOptionArray()
            ]);
            if (isset(json_decode($model->getAttributeTemplate(), true)['fieldaroundby'])) {
                $setIncludeFieldHeader = $model->setData('includefieldheader', json_decode($model->getAttributeTemplate(), true)['includefieldheader']);
            }
        }

        /** @var RendererInterface $rendererBlock */
        $rendererBlock = $this->getLayout()->createBlock(TemplateContent::class)
            ->setTemplate('Magenest_ProductFeed::template/fields_map.phtml');
        $fieldset->addField('fields_map', 'text', [
            'name' => 'fields_map',
            'label' => __('Fields Map'),
            'title' => __('Fields Map'),
        ])->setRenderer($rendererBlock);
        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
