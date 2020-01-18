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

namespace Magenest\ProductFeed\Model\Generator\Template;

use Magenest\ProductFeed\Helper\Data;
use Magenest\ProductFeed\Model\ProductFeed;
use NoSuchElementException;

class GoogleTemplateBuilder
{
    const TEMPLATE_HEADER = 'header_template';
    const TEMPLATE_ITEMS = 'items_template';
    const TEMPLATE_FOOTER = 'footer_template';
    const HEADER_KEYS = ["titlefeed", "description"];

    protected $helper;

    private $_headerFeed = [];
    private $_fieldMaps = [];
    private $_isProcessed = false;

    private $_alreadyBuiltMultiple = [];

    private $_itemsTemplate = "";

    /**
     * GoogleTemplateBuilder constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper,
        ProductFeed $modelFeed
    ) {
        $this->helper = $helper;
    }

    public function buildTemplate($feed, $type)
    {
        $getAttrTemp = $feed->getAttributeTemplate();
        $this->_processFeedTemplate($getAttrTemp);
        switch ($type) {
            case self::TEMPLATE_HEADER:
                $template = $this->buildHeaderTemplate();
                break;
            case self::TEMPLATE_ITEMS:
                if (!$this->_itemsTemplate) {
                    $this->_itemsTemplate = $this->buildItemsTemplate();
                }
                $template = $this->_itemsTemplate;
                break;
            case self::TEMPLATE_FOOTER:
                $template = $this->buildFooterTemplate();
                break;
            default:
                throw new NoSuchElementException(__("Up-supported template file path."));
        }

        return $template;
    }

    public function _processFeedTemplate($jsonTemplate)
    {
        if ($this->_isProcessed) {
            return $this;
        }
        $this->_headerFeed = [];
        $this->_fieldMaps = [];
        $templateArr = $this->helper->unserialize($jsonTemplate);
        if (!is_array($templateArr) || empty($templateArr)) {
            return $this;
        }
        foreach ($templateArr as $key => $template) {
            if (in_array($key, static::HEADER_KEYS)) {
                $this->_headerFeed[$key] = $template;
            }
            if ($key == "fields_map") {
                $this->_processFieldMaps($template);
            }
        }

        return $this;
    }

    protected function _processFieldMaps($fieldMaps)
    {
        if (!is_array($fieldMaps) || empty($fieldMaps)) {
            return [];
        }
        foreach ($fieldMaps as $fieldMap) {
            if ($fieldMap['col_type'] == 'attribute') {
                $this->_fieldMaps = array_merge_recursive($this->_fieldMaps, [$fieldMap['col_attr_temp'] => $fieldMap['col_val']]);
//            } elseif ($fieldMap['col_type'] == 'pattern') {
////                $this->_fieldMaps = array_merge_recursive($this->_fieldMaps, [$fieldMap['col_pattern_val'] => $fieldMap['col_pattern_val']]);
////                $this->_fieldMaps = array_merge_recursive($this->_fieldMaps, [$fieldMap['col_pattern_val']]);
            }
        }

        return $this->_fieldMaps;
    }

    protected function buildHeaderTemplate()
    {
        $html = '<?xml version="1.0" encoding="utf-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"><channel>';
        $headerTitle = isset($this->_headerFeed['titlefeed']) ? $this->_headerFeed['titlefeed'] : "Google Shoppping Feed";
        $html .= $this->buildXml("title", $headerTitle, false);
        $html .= $this->buildXml("link", "{{ store.base_url }}", false);
        $headerDescription = isset($this->_headerFeed['description']) ? $this->_headerFeed['description'] : "";
        $html .= $this->buildXml("description", $headerDescription, false);

        return $html;
    }

    private function buildXml($key, $value, $useCData = true)
    {
        if ($useCData) {
            return "<{$key}><![CDATA[{$value}]]></{$key}>";
        } else {
            return "<{$key}>{$value}</{$key}>";
        }
    }

    protected function buildItemsTemplate()
    {
        $this->_alreadyBuiltMultiple = [];
        $html = "\n{% for product in products %}\n<item>";
        foreach ($this->_fieldMaps as $tag => $value) {
            if (is_array($value)) {
                $value = implode(" ", $value);
            }
            if (strpos($tag, "tax_") === 2 || strpos($tag, "shipping_") === 2) {
                $html .= $this->getMultiLineXml($tag);
                continue;
            }
//            $html .= $tag === "g:additional_image_link" ? "{% for image in product.images %}" : "";
            if ($tag=="g:additional_image_link" && $value == '{{ product.list_product_image }}') {
                $html .= "{% for image in product.images %}";
                $html .= $this->buildXml($tag, "{{ image.url }}");
                $html .="{% endfor %}";
            } else {
                $html .= $this->buildXml($tag, $value);
            }
        }
        $html .= "</item>\n{% endfor %}";

        return $html;
    }

    private function getMultiLineXml($tag)
    {
        $html = "";
        $type = $this->getTypeByTag($tag);
        if (!$type || in_array($type, $this->_alreadyBuiltMultiple)) {
            return $html;
        }
        $html .= "<{$type}>";
        foreach ($this->getMultiTagByType($type) as $tag => $value) {
            if (is_array($value)) {
                $value = implode(" ", $value);
            }
            $html .= $this->buildXml($tag, $value);
        }
        $html .= "</{$type}>";
        $this->_alreadyBuiltMultiple[] = $type;

        return $html;
    }

    private function getTypeByTag($tag)
    {
        $strArr = explode("_", $tag);
        if (is_array($strArr) && isset($strArr[0])) {
            return $strArr[0];
        }

        return false;
    }

    private function getMultiTagByType($type)
    {
        $result = [];
        if (!is_array($this->_fieldMaps) || empty($this->_fieldMaps)) {
            return $result;
        }
        foreach ($this->_fieldMaps as $key => $value) {
            if (strpos($key, $type) !== 0 || strpos($key, $type) === false) {
                continue;
            }
            $tagName = "g:" . substr($key, strlen($type) + 1);
            $result = array_merge_recursive($result, [$tagName => $value]);
        }

        return $result;
    }

    protected function buildFooterTemplate()
    {
        return "</channel></rss>";
    }
}
