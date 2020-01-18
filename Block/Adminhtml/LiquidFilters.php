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

namespace Magenest\ProductFeed\Block\Adminhtml;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class LiquidFilters
 *
 * @package Magenest\ProductFeed\Block\Adminhtml
 */
class LiquidFilters
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * LiquidFilters constructor.
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param $subject
     * @return string
     * @throws NoSuchEntityException
     */
    public function price($subject)
    {
        return $subject . ' ' . $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Auto correct for csv,txt file
     *
     * @param $subject
     * @param $fieldAround
     * @param $fieldSeparate
     * @return mixed
     */
    public function mpCorrect($subject, $fieldAround, $fieldSeparate)
    {
        switch ($fieldAround) {
            case 'quotes':
            case 'quote':
                $result = str_replace('"', "'", $subject);
                break;
            default:
                $result = str_replace("\n", "\t", $subject);
                switch ($fieldSeparate) {
                    case ';':
                        $result = str_replace(";", ",", $result);
                        break;
                    case ',':
                        $result = str_replace(",", ";", $result);
                        break;
                    default:
                        $result = str_replace("\t", " ", $result);
                }
        }

        return $result;
    }

    /**
     * @param $subject
     * @return int
     */
    public function count($subject)
    {
        return count($subject);
    }

    /**
     * @return array
     */
    public function getFiltersMethods()
    {
        return array_keys($this->getFilters());
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        $filters = [
            'replace' => ['label' => __('Replace'), 'params' => [['label' => __('Search'), 'defVal' => ''], ['label' => __('Replace'), 'defVal' => '']]],
            'replace_first' => ['label' => __('Replace First'), 'params' => [['label' => __('Search'), 'defVal' => ''], ['label' => __('Replace'), 'defVal' => '']]],
            'slice' => ['label' => __('Slice'), 'params' => [['label' => __('From'), 'defVal' => ''], ['label' => __('To'), 'defVal' => '']]],
            'truncate' => ['label' => __('Truncate'), 'params' => [['label' => __('Count'), 'Chars' => ''], ['label' => __('Append Last'), 'defVal' => '']]],
            'truncatewords' => ['label' => __('Truncate Words'), 'params' => [['label' => __('Words'), 'defVal' => ''], ['label' => __('Append Last'), 'defVal' => '']]],
            'strtolower' => ['label' => __('Lowercase'), 'params' => []],
            'ceil' => ['label' => __('Ceil'), 'params' => []],
            'strtoupper' => ['label' => __('Uppercase'), 'params' => []],
            'ucfirst' => ['label' => __('Capitalize'), 'params' => []],
            'upcase' => ['label' => __('Uppercase'), 'params' => []],
            'ucwords' => ['label' => __('Uppercase first character of each word '), 'params' => []],
            'append' => ['label' => __('Append'), 'params' => [['label' => __('Append'), 'defVal' => '']]],
            'prepend' => ['label' => __('Prepend'), 'params' => [['label' => __('Prepend'), 'defVal' => '']]],
            'at_least' => ['label' => __('At Least'), 'params' => [['label' => __('At Least'), 'defVal' => '']]],
            'at_most' => ['label' => __('At Most'), 'params' => [['label' => __('At Most'), 'defVal' => '']]],
            'date' => ['label' => __('Date'), 'params' => [['label' => __('Date Format'), 'defVal' => '']]],
            'default' => ['label' => __('Default'), 'params' => [['label' => __('Default'), 'defVal' => '']]],
            'divided_by' => ['label' => __('Divided By'), 'params' => [['label' => __('Divided By'), 'defVal' => '']]],
            'plus' => ['label' => __('Plus'), 'params' => [['label' => __('Plus'), 'defVal' => '']]],
            'remove' => ['label' => __('Remove'), 'params' => [['label' => __('Remove'), 'defVal' => '']]],
            'join' => ['label' => __('Join'), 'params' => [['label' => __('Join By'), 'defVal' => '']]],
            'minus' => ['label' => __('Minus'), 'params' => [['label' => __('Minus'), 'defVal' => '']]],
            'modulo' => ['label' => __('Modulo'), 'params' => [['label' => __('Divided By'), 'defVal' => '']]],
            'times' => ['label' => __('Times'), 'params' => [['label' => __('Times'), 'defVal' => '']]],
            'abs' => ['label' => __('Abs'), 'params' => []],
            'capitalize' => ['label' => __('Abs'), 'params' => []],
            'downcase' => ['label' => __('Down Case'), 'params' => []],
            'escape' => ['label' => __('Escape'), 'params' => []],
            'escape_once' => ['label' => __('Escape once'), 'params' => []],
            'floor' => ['label' => __('Floor'), 'params' => []],
            'lstrip' => ['label' => __('Left Trim'), 'params' => []],
            'newline_to_br' => ['label' => __('Replace new line to <br'), 'params' => []],
            'reverse' => ['label' => __('Reverse Array'), 'params' => []],
            'rstrip' => ['label' => __('Right Trim'), 'params' => []],
            'size' => ['label' => __('Array Size'), 'params' => []],
            'sort' => ['label' => __('Array Sort'), 'params' => []],
            'strip' => ['label' => __('Trim Text'), 'params' => []],
            'strip_html' => ['label' => __('Strip Html Tags'), 'params' => []],
            'strip_newlines' => ['label' => __('Strip New Line'), 'params' => []],
            'uniq' => ['label' => __('Unique Id Of Array'), 'params' => []],
            'url_decode' => ['label' => __('Decode Url'), 'params' => []],
            'url_encode' => ['label' => __('Encode Url'), 'params' => []],
            'override' => ['label' => __('Override'), 'params' => []],
        ];

        $customFilter = [
            'count' => ['label' => __('Count'), 'params' => []],
            'price' => ['label' => __('Price'), 'params' => []],
            'ifEmpty' => ['label' => __('If Empty'), 'params' => [['label' => __('Default'), 'defVal' => '']]],
        ];

        return array_merge($filters, $customFilter);
    }

    /**
     * @param $subject
     * @param $default
     * @return mixed
     */
    public function ifEmpty($subject, $default)
    {
        if (!$subject) {
            $subject = $default;
        }

        return $subject;
    }

    public function attrGoogleXml()
    {
        $attrGoogleXml = "
        {
            \"titlefeed\":\"\",
            \"description\":\"\",
            \"fields_map\":{
            \"1572834929405_405\":{
                \"col_attr_temp\":\"g:id\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"sku\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.sku }}\"
        },
            \"1572834930060_60\":{
                \"col_attr_temp\":\"title\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"name\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.name }}\"
            },
            \"1572834930580_580\":{
                \"col_attr_temp\":\"description\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"description\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.description | strip_html }}\",
                \"modifiers\":{
                    \"1574646906536_536\":{
                    \"value\":\"strip_html\"
                    }
                }
            },   
            \"1572835385335_335\":{
                \"col_attr_temp\":\"g:link\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"link\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.link }}\"
            },   
            \"1572834931828_828\":{
                \"col_attr_temp\":\"g:image_link\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"image_link\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.image_link }}\"
            },    
            \"1572834932836_836\":{
                \"col_attr_temp\":\"g:additional_image_link\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"0\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.0 }}\"
            },   
            \"1572834933756_756\":{
                \"col_attr_temp\":\"g:condition\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"0\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.0 | default: 'New' }}\",
                \"modifiers\":{
                \"1574646889190_190\":{
                    \"value\":\"default\",
                    \"params\":[
                        \"New\"
                        ]
                    }
                }
            },    
            \"1572834934852_852\":{
                \"col_attr_temp\":\"g:availability\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"quantity_and_stock_status\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.quantity_and_stock_status }}\"
            },  
            \"1572834935923_923\":{
                \"col_attr_temp\":\"g:price\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"price\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.price }}\"
            },
            \"1572834936900_900\":{
                \"col_attr_temp\":\"g:google_product_category\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"mapping\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.mapping }}\"
            },       
            \"1572834937892_892\":{
                \"col_attr_temp\":\"g:product_type\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"category_path\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.category_path }}\"
            }, 
            \"1572834939188_188\":{
                \"col_attr_temp\":\"g:brand\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"manufacturer\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.manufacturer | ifEmpty: 'DefaultBrand' }}\",
                \"modifiers\":{
                \"1574648178513_513\":{
                \"value\":\"ifEmpty\",
                        \"params\":[
                            \"DefaultBrand\"
                        ]
                    }
                }
            },
            \"1572834940227_227\":{
                \"col_attr_temp\":\"g:identifier_exists\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"0\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.0 | default: 'FALSE' }}\",
                \"modifiers\":{
                \"1574646930620_620\":{
                    \"value\":\"default\",
                    \"params\":[
                        \"FALSE\"
                        ]
                    }
                }
            },       
            \"1572834941133_133\":{
                \"col_attr_temp\":\"g:tax_country\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"0\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.0 | default: 'US' }}\",
                \"modifiers\":{
                \"1574646948260_260\":{
                    \"value\":\"default\",
                    \"params\":[
                        \"US\"
                        ]
                    }
                }
            },    
            \"1572834941938_938\":{
                \"col_attr_temp\":\"g:tax_rate\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"0\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.0 | default: '0' }}\",
                \"modifiers\":{
                \"1574647008341_341\":{
                    \"value\":\"default\",
                    \"params\":[
                        \"0\"
                        ]
                    }
                }
            },
            \"1572834942771_771\":{
                \"col_attr_temp\":\"g:tax_tax_ship\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"0\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.0 | default: 'n' }}\",
                \"modifiers\":{
                \"1574647024253_253\":{
                    \"value\":\"default\",
                        \"params\":[
                            \"n\"
                        ]
                    }
                }
            },
            \"1572834943772_772\":{
                \"col_attr_temp\":\"g:shipping_country\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"0\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.0 | default: 'US' }}\",
                \"modifiers\":{
                    \"1574647040904_904\":{
                        \"value\":\"default\",
                        \"params\":[
                            \"US\"
                        ]
                    }
                }
            },
            \"1572834944788_788\":{
                \"col_attr_temp\":\"g:shipping_price\",
                \"col_type\":\"attribute\",
                \"col_attr_val\":\"0\",
                \"col_pattern_val\":\"\",
                \"col_val\":\"{{ product.0 | default: '0 USD' }}\",
                \"modifiers\":{
                    \"1574647056665_665\":{
                        \"value\":\"default\",
                        \"params\":[
                            \"0 USD\"
                        ]
                    }
                }
            }
            }
        }";
        return $attrGoogleXml;
    }


    public function attrFacebookCsv()
    {
        $attrFacebookCSV = "{
          \"fields_map\": {
            \"1572834929405_405\": {
              \"col_name\": \"id\",
              \"col_type\": \"attribute\",
              \"col_attr_val\": \"sku\",
              \"col_pattern_val\": \"\",
              \"col_val\": \"{{ product.sku }}\"
            },
            \"1572834930060_60\": {
              \"col_name\": \"name\",
              \"col_type\": \"attribute\",
              \"col_attr_val\": \"name\",
              \"col_pattern_val\": \"\",
              \"col_val\": \"{{ product.name }}\"
            },
            \"1572834930580_580\": {
              \"col_name\": \"description\",
              \"col_type\": \"attribute\",
              \"col_attr_val\": \"description\",
              \"col_pattern_val\": \"\",
              \"col_val\": \"{{ product.description | strip_html }}\",
              \"modifiers\": {
                \"1574492941790_790\": {
                  \"value\": \"strip_html\"
                }
              }
            },
            \"1572835385335_335\": {
              \"col_name\": \"link\",
              \"col_type\": \"attribute\",
              \"col_attr_val\": \"link\",
              \"col_pattern_val\": \"\",
              \"col_val\": \"{{ product.link }}\"
            },
            \"1572834931828_828\": {
              \"col_name\": \"image link\",
              \"col_type\": \"attribute\",
              \"col_attr_val\": \"image_link\",
              \"col_pattern_val\": \"\",
              \"col_val\": \"{{ product.image_link }}\"
            },
            \"1572834934852_852\": {
              \"col_name\": \"quantity\",
              \"col_type\": \"attribute\",
              \"col_attr_val\": \"quantity_and_stock_status\",
              \"col_pattern_val\": \"\",
              \"col_val\": \"{{ product.quantity_and_stock_status }}\"
            },
            \"1572834935923_923\": {
              \"col_name\": \"price\",
              \"col_type\": \"attribute\",
              \"col_attr_val\": \"price\",
              \"col_pattern_val\": \"\",
              \"col_val\": \"{{ product.price }}\"
            },
            \"1572834936900_900\": {
              \"col_name\": \"mapping\",
              \"col_type\": \"attribute\",
              \"col_attr_val\": \"mapping\",
              \"col_pattern_val\": \"\",
              \"col_val\": \"{{ product.mapping }}\"
            },
            \"1572834937892_892\": {
              \"col_name\": \"category path\",
              \"col_type\": \"attribute\",
              \"col_attr_val\": \"category_path\",
              \"col_pattern_val\": \"\",
              \"col_val\": \"{{ product.category_path }}\"
            },
            \"1572834939188_188\": {
              \"col_name\": \"manufacture\",
              \"col_type\": \"attribute\",
              \"col_attr_val\": \"manufacturer\",
              \"col_pattern_val\": \"\",
              \"col_val\": \"{{ product.manufacturer }}\"
            }
          }
        }";

        return $attrFacebookCSV;
    }

    public function attrTempGoogle()
    {
        $attrTemplate = [
            'g:id' => ['label' => __('g:id'), 'value' => 'g:id'],
            'title' => ['label' => __('title'), 'value' => 'title'],
            'description' => ['label' => __('description'), 'value' => 'description'],
            'g:link' => ['label' => __('g:link'), 'value' => 'g:link'],
            'g:image_link' => ['label' => __('g:image_link'), 'value' => 'g:image_link'],
            'g:additional_image_link' => ['label' => __('g:additional_image_link'), 'value' => 'g:additional_image_link'],
            'g:condition' => ['label' => __('g:condition'), 'value' => 'g:condition'],
            'g:availability' => ['label' => __('g:availability'), 'value' => 'g:availability'],
            'g:price' => ['label' => __('g:price'), 'value' => 'g:price'],
            'g:google_product_category' => ['label' => __('g:google_product_category'), 'value' => 'g:google_product_category'],
            'g:product_type' => ['label' => __('g:product_type'), 'value' => 'g:product_type'],
            'g:brand' => ['label' => __('g:brand'), 'value' => 'g:brand'],
            'g:identifier_exists' => ['label' => __('g:identifier_exists'), 'value' => 'g:identifier_exists'],
            'g:tax_country' => ['label' => __('g:tax_country'), 'value' => 'g:tax_country'],
            'g:tax_rate' => ['label' => __('g:tax_rate'), 'value' => 'g:tax_rate'],
            'g:tax_tax_ship' => ['label' => __('g:tax_tax_ship'), 'value' => 'g:tax_tax_ship'],
            'g:shipping_country' => ['label' => __('g:shipping_country'), 'value' => 'g:shipping_country'],
            'g:shipping_price' => ['label' => __('g:shipping_price'), 'value' => 'g:shipping_price'],
        ];

        return $attrTemplate;
    }
}
