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

namespace Magenest\ProductFeed\Block\Adminhtml\Template\Edit\Tab\Renderer;

use Magenest\ProductFeed\Block\Adminhtml\LiquidFilters;
use Magenest\ProductFeed\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;

class TemplateContent extends Element implements RendererInterface
{
    /**
     * @var array
     */
    public $primaryAttr = [
        'attribute_set', 'description', 'status', 'meta_description', 'meta_keyword', 'meta_title',
        'entity_id', 'name', 'type_id', 'url', 'sku', 'short_description', 'url_key', 'visibility'
    ];

    /**
     * @var array
     */
    public $priceTaxAttr = [
        'cost', 'msrp_display_actual_price_type', 'price_type', 'final_price', 'msrp',
        'minimal_price', 'price', 'price_view', 'regular_price', 'special_price', 'special_from_date',
        'special_to_date', 'tax_class_id', 'tier_price'
    ];

    /**
     * @var array
     */
    public $catAttr = ['category_ids', 'category.entity_id', 'category', 'category.path'];

    /**
     * @var array
     */
    public $imgAttr = [
        'image', 'image_label', 'small_image_label', 'small_image', 'swatch_image', 'thumbnail_label', 'thumbnail'
    ];

    /**
     * @var array
     */
    public $stockAttr = ['is_in_stock', 'qty', 'quantity_and_stock_status', 'quantity_and_stock_status_qty'];

    /**
     * @var array
     */
    public $otherAttr = [];

    /**
     * @var string $_template
     */
    protected $_template = 'Magenest_ProductFeed::template/template_content.phtml';

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var LiquidFilters
     */
    protected $liquidFilters;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    public function __construct(
        Context $context,
        Attribute $eavAttribute,
        Registry $registry,
        JsonHelper $jsonHelper,
        Data $helperData,
        LiquidFilters $liquidFilters,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->jsonHelper = $jsonHelper;
        $this->helperData = $helperData;
        $this->liquidFilters = $liquidFilters;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        $html = $this->toHtml();

        return $html;
    }

    /**
     * @return null
     */
    public function getFieldsMap()
    {
        $templateType = $this->helperData->getTemplate();
        $feed = $this->registry->registry('information');
        if ($templateType == 'facebook') {
            $fieldsDefault = $this->liquidFilters->attrFacebookCsv();
        } else {
            $fieldsDefault = $this->liquidFilters->attrGoogleXml();
        }
        $fieldsMap = $feed->getAttributeTemplate();
        if (!$fieldsMap) {
            $fieldsMap = $feed->setAttributeTemplate($fieldsDefault);
        }

        return $fieldsMap;
    }


    /**
     * @return array
     */
    public function getEavAttrCollection()
    {
        $collection = $this->eavAttribute->getCollection()
            ->addFieldToFilter(AttributeSet::KEY_ENTITY_TYPE_ID, 4);
        $attrCollection = [
            'primary' => ['label' => __('Primary Attributes'), 'values' => []],
            'price_tax' => ['label' => __('Price Tax Attributes'), 'values' => []],
            'cat' => ['label' => __('Category Attributes'), 'values' => []],
            'image' => ['label' => __('Image Attributes'), 'values' => []],
            'stock' => ['label' => __('Stock Attributes'), 'values' => []],
            'other' => ['label' => __('Other Attributes'), 'values' => []],
        ];

        /** @var \Magento\Eav\Model\Entity\Attribute $item */
        foreach ($collection as $item) {
            switch (true) {
                case in_array($item->getAttributeCode(), $this->primaryAttr):
                    $attrCollection['primary']['values'][] = $item;
                    break;
                case in_array($item->getAttributeCode(), $this->priceTaxAttr):
                    $attrCollection['price_tax']['values'][] = $item;
                    break;
                case in_array($item->getAttributeCode(), $this->catAttr):
                    $attrCollection['cat']['values'][] = $item;
                    break;
                case in_array($item->getAttributeCode(), $this->imgAttr):
                    $attrCollection['image']['values'][] = $item;
                    break;
                case in_array($item->getAttributeCode(), $this->stockAttr):
                    $attrCollection['stock']['values'][] = $item;
                    break;
                default:
                    $attrCollection['other']['values'][] = $item;
                    break;
            }
        }

        $productLink = new DataObject([
            'attribute_id' => 'select-attrpf_link',
            'attribute_code' => 'link',
            'default_frontend_label' => __('Product Link'),
        ]);
        $finalPrice = new DataObject([
            'attribute_id' => 'pf_final_price',
            'attribute_code' => 'final_price',
            'default_frontend_label' => __('Final Price'),
        ]);
        $imageLink = new DataObject([
            'attribute_id' => 'pf_image_link',
            'attribute_code' => 'image_link',
            'default_frontend_label' => __('Product Image Link'),
        ]);
        $categoryPath = new DataObject([
            'attribute_id' => 'pf_category_path',
            'attribute_code' => 'category_path',
            'default_frontend_label' => __('Product Category Path'),
        ]);
        $productId = new DataObject([
            'attribute_id' => 'pf_product_id',
            'attribute_code' => 'entity_id',
            'default_frontend_label' => __('Product Id'),
        ]);
        $productQty = new DataObject([
            'attribute_id' => 'pf_product_id',
            'attribute_code' => 'entity_id',
            'default_frontend_label' => __('Product Id'),
        ]);
        $mapping = new DataObject([
            'attribute_id' => 'pf_product_mapping',
            'attribute_code' => 'mapping',
            'default_frontend_label' => __('Product Mapping'),
        ]);
        $listProductImage = new DataObject([
            'attribute_id' => 'pf_list_product_image',
            'attribute_code' => 'list_product_image',
            'default_frontend_label' => __('List Product Image'),
        ]);
        $attrCollection['image']['values'][] = $listProductImage;
        $attrCollection['primary']['values'][] = $productLink;
        $attrCollection['primary']['values'][] = $imageLink;
        $attrCollection['primary']['values'][] = $categoryPath;
        $attrCollection['primary']['values'][] = $productId;
        $attrCollection['primary']['values'][] = $mapping;
        $attrCollection['other']['values'][] = $finalPrice;
        $attrCollection['stock']['values'][] = $productQty;

        return $attrCollection;
    }

    /**
     * @return array
     */
    public function getModifier()
    {
        return $this->liquidFilters->getFilters();
    }

    public function getElement()
    {
        return parent::getElement(); // TODO: Change the autogenerated stub
    }

    public function helperDataString($data)
    {
        return $this->helperData->serialize($data);
    }

    public function getAttrTemplate()
    {
        return $this->liquidFilters->attrTempGoogle();
    }
}
