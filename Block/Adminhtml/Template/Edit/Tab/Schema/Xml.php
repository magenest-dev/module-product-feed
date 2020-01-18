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

namespace Magenest\ProductFeed\Block\Adminhtml\Template\Edit\Tab\Schema;

use Magenest\ProductFeed\Helper\Output as OutputHelper;
use Magenest\ProductFeed\Model\Templates;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Registry;

class Xml extends Form
{
    /**
     * @var Registry
     */
    protected $registry;

    protected $outputHelper;

    /**
     * {@inheritdoc}
     */


    public function __construct(
        Context $context,
        Registry $registry,
        OutputHelper $outputHelper,
        Form\Element\ElementCreator $creator = null,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->outputHelper = $outputHelper;
        $this->_template = 'Magenest_ProductFeed::template/edit/tab/schema/xml.phtml';
        parent::__construct($context, $data, $creator);
    }

    /**
     * @return array
     */
    public function getJsConfig()
    {
        $liquidTemplate = '';
        if ($this->getModel()) {
            $liquidTemplate = $this->getModel();
        }

        return [
            "*" => [
                'Magento_Ui/js/core/app' => [
                    'components' => [
                        'schema_xml' => [
                            'component' => 'Magenest_ProductFeed/js/edit/tab/schema/xml',
                            'config' => [
                                'liquidTemplate' => $liquidTemplate
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Current template or feed model
     *
     * @return Templates
     */
    public function getModel()
    {
        return $this->registry->registry('templates');
    }
}
