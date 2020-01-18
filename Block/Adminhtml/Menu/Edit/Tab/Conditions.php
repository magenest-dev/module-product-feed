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

use Magenest\ProductFeed\Model\ProductFeed;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Conditions extends Generic implements TabInterface
{
    /**
     * Core registry
     *
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;

    private $ruleFactory;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        /** @var ProductFeed $model */
        $model = $this->_coreRegistry->registry('information');
        if (!$model) {
            $id = $this->getRequest()->getParam('rule_id');
            $model = $this->getRuleFactory()->create();
            $model->load($id);
        }
        $fieldsetId = 'conditions_fieldset';
        $formName = 'catalog_rule_form';

        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $newChildUrl = $this->getUrl(
            'catalog_rule/promo_catalog/newConditionHtml/form/' . $model->getConditionsFieldSetId($formName),
            ['form_namespace' => $formName]
        );

        $renderer = $this->_rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($model->getConditionsFieldSetId($formName));

        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __(
                    'Apply the rule only if the following conditions are met (leave blank for all products).'
                )
            ]
        )->setRenderer(
            $renderer
        );


        $fieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $model
        )->setRenderer(
            $this->_conditions
        );

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getConditions(), $formName);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getRuleFactory()
    {
        if ($this->ruleFactory === null) {
            $this->ruleFactory = ObjectManager::getInstance()->get('Magento\CatalogRule\Model\RuleFactory');
        }
        return $this->ruleFactory;
    }
}
