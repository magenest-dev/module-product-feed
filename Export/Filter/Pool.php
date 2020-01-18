<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_productfeed extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_productfeed
 */

namespace Magenest\ProductFeed\Export\Filter;

use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;

class Pool
{
    /**
     * @var object[]
     */
    protected $scopes=[];

    /**
     * Constructor
     *
     * @param array $scopes
     */

    /**
     * List of scopes
     *
     * @return object[]
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Return full list of possible filters
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getFilters()
    {
        $filters = [];
        foreach ($this->scopes as $scope) {
            $class = new ClassReflection($scope);

            /** @var MethodReflection $method */
            foreach ($class->getMethods() as $method) {
                try {
                    $doc = $method->getDocblock();
                } catch (\Exception $e) {
                    continue;
                }
                $filter = ['label' => __($doc->getShortDescription())->__toString(), 'value' => $method->getName(), 'args' => [],];

                /** @var \Zend_Server_Reflection_Parameter $param */
                foreach ($method->getParameters() as $param) {
                    if ($param->getName() == 'input') {
                        continue;
                    }
                    $default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : '';
                    $filter['args'][] = ['value' => $param->getName(), 'label' => ucfirst($param->getName()), 'default' => $default];
                }
                $filters[] = $filter;
            }
        }

        return $filters;
    }
}
