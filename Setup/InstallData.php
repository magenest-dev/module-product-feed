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

namespace Magenest\ProductFeed\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $sampleTemplates = [];
        $sampleTemplates[] = [
            'name' => 'google_shopping_xml',
            'title' => 'Google Shopping XML',
            'type' => 'xml',
            'content' =>
                '<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
  <channel>
    <title>Google Shoppping Feed</title>
    <link>{{ store.base_url }}</link>
    <description>This is description</description>
    {% for product in products %}
    <item>
      <g:id><![CDATA[{{ product.sku }}]]></g:id>
      <title><![CDATA[{{ product.name | strip_html | truncate: \'155\' }}]]></title>
      <description><![CDATA[{{ product.description | strip_html | truncate: \'600\' }}]]></description>
      <link><![CDATA[{{ product.link }}]]></link>
      <g:image_link><![CDATA[{{ product.image_link }}]]></g:image_link>
      {% for image in product.images %}
        <g:additional_image_link><![CDATA[{{ image.url }}]]></g:additional_image_link>
      {% endfor %}
      <g:condition>New</g:condition>
      <g:availability>{{ product.quantity_and_stock_status }}</g:availability>
      <g:price>{{ product.final_price | price }}</g:price>
      <g:google_product_category><![CDATA[{{ product.mapping }}]]></g:google_product_category>
      <g:product_type><![CDATA[{{ product.category_path }}]]></g:product_type>
      <g:brand><![CDATA[{{ product.manufacturer | ifEmpty: \'DefaultBrand\' }}]]></g:brand>
      <g:identifier_exists>FALSE</g:identifier_exists>
      <g:tax>
       <g:country>US</g:country>
       <g:rate>0</g:rate>
       <g:tax_ship>n</g:tax_ship>
      </g:tax>
      <g:shipping>
        <g:country>US</g:country>
        <g:price>0 USD</g:price>
      </g:shipping>
            

    </item>
    {% endfor %}
  </channel>
</rss>'
        ];

        $setup->getConnection()->insertMultiple($setup->getTable('magenest_product_feed_template'), $sampleTemplates);
        $installer->endSetup();
    }
}
