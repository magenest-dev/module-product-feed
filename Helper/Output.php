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

namespace Magenest\ProductFeed\Helper;

use Magenest\ProductFeed\Export\Filter\Pool as FilterPool;
use Magenest\ProductFeed\Export\Resolver\Pool as ResolverPool;
use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use ReflectionException;

class Output extends AbstractHelper
{
    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var ResolverPool
     */
    protected $resolverPool;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $operatorInputByType = ['string' => ['==', '!=', '>=', '>', '<=', '<', '{}', '!{}'], 'numeric' => ['==', '!=', '>=', '>', '<=', '<'], 'date' => ['==', '>=', '<='], 'select' => ['==', '!='], 'boolean' => ['==', '!='], 'multiselect' => ['{}', '!{}', '()', '!()'], 'grid' => ['()', '!()'],];

    /**
     * @var array
     */
    protected $operatorOptions = ['==' => 'is', '!=' => 'is not', '>=' => 'equals or greater than', '<=' => 'equals or less than', '>' => 'greater than', '<' => 'less than', '{}' => 'contains', '!{}' => 'does not contain', '()' => 'is one of', '!()' => 'is not one of',];

    /**
     * Output constructor.
     *
     * @param FilterPool $filterPool
     * @param ResolverPool $resolverPool
     * @param ObjectManagerInterface $objectManager
     * @param Context $context
     */
    public function __construct(
        FilterPool $filterPool,
        ResolverPool $resolverPool,
        ObjectManagerInterface $objectManager,
        Context $context
    ) {
        $this->filterPool = $filterPool;
        $this->resolverPool = $resolverPool;
        $this->objectManager = $objectManager;

        parent::__construct($context);
    }

    /**
     * List of defined attributes to export
     *
     * @return array
     */
    public function getAttributeOptions()
    {
        $options = [];

        foreach ($this->resolverPool->getResolvers() as $resolver) {
            $attributes = $resolver->getAttributes();

            asort($attributes);

            foreach ($attributes as $code => $label) {
                $group = $this->getAttributeGroup($code);
                $options[$group]['label'] = $group;
                $options[$group]['value'][] = ['value' => $code, 'label' => $label];
            }
        }

        usort(
            $options,
            function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            }
        );

        return array_values($options);
    }

    /**
     * Attribute group
     *
     * @param string $code
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAttributeGroup($code)
    {
        $primary = ['attribute_set', 'attribute_set_id', 'entity_id', 'full_description', 'meta_description', 'meta_keyword', 'meta_title', 'name', 'short_description', 'description', 'sku', 'status', 'status_parent', 'url', 'url_key', 'visibility', 'type_id'];

        $stock = ['is_in_stock', 'qty', 'qty_children', 'manage_stock', 'stock_status',];

        $price = ['tax_class_id', 'special_from_date', 'special_to_date', 'cost', 'msrp',];

        if (in_array($code, $primary)) {
            $group = __('1. Primary Attributes');
        } elseif (in_array($code, $stock)) {
            $group = __('5. Stock Attributes');
        } elseif (strpos($code, 'inventory') !== false) {
            $group = __('6. Multi Source Inventory Sources');
        } elseif (in_array($code, $price) || strpos($code, 'price') !== false) {
            $group = __('2. Prices & Taxes');
        } elseif (strpos($code, 'image') !== false || strpos($code, 'thumbnail') !== false) {
            $group = __('4. Images');
        } elseif (strpos($code, 'category') !== false) {
            $group = __('3. Category');
        } elseif (strpos($code, 'dynamic') !== false) {
            $group = __('7. Dynamic Attributes');
        } elseif (strpos($code, 'variable') !== false) {
            $group = __('8. Dynamic Variables');
        } elseif (strpos($code, 'mapping') === 0) {
            $group = __('9. Category Mappings');
        } else {
            $group = __('Others Attributes');
        }

        return $group->__toString();
    }

    /**
     * List of pattern types
     *
     * @return array
     */
    public function getPatternTypeOptions()
    {
        return [['label' => 'Pattern', 'value' => 'pattern'], ['label' => 'Attribute', 'value' => ''], ['label' => 'Parent Product', 'value' => 'parent'], ['label' => 'Only Parent Product', 'value' => 'only_parent',], ['label' => 'Grouped Product', 'value' => 'grouped',],];
    }

    /**
     * List of filters
     *
     * @return array
     * @throws ReflectionException
     */
    public function getFilterOptions()
    {
        return $this->filterPool->getFilters();
    }

    /**
     * @param string $attributeCode
     * @return array
     */
    public function getAttributeOperators($attributeCode)
    {
        $conditions = [];

        $attribute = $this->getAttribute($attributeCode);

        $type = 'string';

        if ($attribute) {
            switch ($attribute->getFrontendInput()) {
                case 'select':
                    $type = 'select';
                    break;

                case 'multiselect':
                    $type = 'multiselect';
                    break;

                case 'date':
                    $type = 'date';
                    break;

                case 'boolean':
                    $type = 'boolean';
                    break;

                default:
                    $type = 'string';
            }
        }
        foreach ($this->operatorInputByType[$type] as $operator) {
            $operatorTitle = __($this->operatorOptions[$operator]);
            $conditions[] = ['label' => $operatorTitle, 'value' => $operator,];
        }

        return $conditions;
    }

    /**
     * @param string $code
     * @return Attribute|false
     */
    protected function getAttribute($code)
    {
        $entityTypeId = $this->objectManager->get('Magento\Eav\Model\Entity')->setType('catalog_product')->getTypeId();

        /** @var Collection $attributes */
        $attributes = $this->objectManager->create(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection'
        )->setEntityTypeFilter($entityTypeId);

        $attribute = $attributes->getItemByColumnValue('attribute_code', $code);

        if ($attribute) {
            return $this->objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->setEntityTypeId(
                $entityTypeId
            )->load($attribute->getId());
        }

        return false;
    }

    /**
     * @param string $attributeCode
     * @return array
     */
    public function getAttributeValues($attributeCode)
    {
        $result = [];

        $attribute = $this->getAttribute($attributeCode);
        if ($attribute) {
            if ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
                $result[] = ['label' => __('not set'), 'value' => ''];
                foreach ($attribute->getSource()->getAllOptions() as $option) {
                    $result[] = ['label' => $option['label'], 'value' => $option['value'],];
                }
            }
        }

        return $result;
    }
}
