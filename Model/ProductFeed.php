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

namespace Magenest\ProductFeed\Model;

use Magento\Backend\Model\Session;
use Magenest\ProductFeed\Helper\Data;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory as CatalogCategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\App\EmulationFactory;

class ProductFeed extends AbstractModel
{
    protected $session;

    protected $request;

    protected $helperData;

    protected $resourceIterator;

    protected $_productFactory;

    protected $_productCollectionFactory;

    protected $productIds;

    /** @var Session */
    protected $sessionBackend;
    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var array
     */
    protected $assocMapping;

    protected $categoryFactory;

    protected $emulationFactory;
    /** @var ScopeConfigInterface */
    protected $scopeConfig;
    private $_emulationInstance = null;

    /**
     * ProductFeed constructor.
     *
     * @param CatalogCategoryFactory $categoryFactory
     * @param Context $context
     * @param Session $sessionBackend
     * @param RequestInterface $request
     * @param CollectionFactory $_productCollectionFactory
     * @param Data $helperData
     * @param Iterator $resourceIterator
     * @param ProductFactory $_productFactory
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param ScopeConfigInterface $scopeConfig
     * @param EmulationFactory $emulationFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        CatalogCategoryFactory $categoryFactory,
        Context $context,
        Session $sessionBackend,
        RequestInterface $request,
        CollectionFactory $_productCollectionFactory,
        Data $helperData,
        Iterator $resourceIterator,
        ProductFactory $_productFactory,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        ScopeConfigInterface $scopeConfig,
        EmulationFactory $emulationFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        Json $serializer = null,
        array $data = []
    ) {
        $this->request = $request;
        $this->categoryFactory = $categoryFactory;
        $this->helperData = $helperData;
        $this->resourceIterator = $resourceIterator;
        $this->_productFactory = $_productFactory;
        $this->_productCollectionFactory = $_productCollectionFactory;
        $this->emulationFactory = $emulationFactory;
        $this->scopeConfig = $scopeConfig;
        $this->sessionBackend  =   $sessionBackend;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data, $extensionFactory, $customAttributeFactory, $serializer);
    }

    /**
     * @return Combine|\Magento\Rule\Model\Action\Collection|\Magento\Rule\Model\Condition\Combine
     * Getter for rule conditions collection
     */
    public function getConditionsInstance()
    {
        return $this->getActionsInstance();
    }

    /**
     * @return Combine|\Magento\Rule\Model\Action\Collection
     */
    public function getActionsInstance()
    {
        return ObjectManager::getInstance()->create(Combine::class);
    }
    // get file name of feed
    public function getFileName()
    {
        return $this->getData('filename') . '.' . strtolower($this->getData('filetype'));
    }
    // get id of product from filter
    public function getMatchingProductIds()
    {
        if ($this->productIds === null) {
            $data = $this->request->getPost('rule');
            $storeId = isset($this->request->getPost('feed')['store_id']) ? $this->request->getPost('feed')['store_id'] : null;
            if ($data) {
                $this->sessionBackend->setProductFeedData(['rule' => $data, 'store_id' => $storeId]);
            } else {
                $productFeedData = $this->sessionBackend->getProductFeedData();
                $data = $productFeedData['rule'];
                $storeId = $productFeedData['store_id'];
            }
            if (!$data) {
                $data = [];
            }
            $this->productIds = [];
            $this->setCollectedAttributes([]);

            /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $productCollection = $this->_productFactory->create()->getCollection();
            $productCollection->addAttributeToSelect('*')
                ->addAttributeToFilter('status', 1)->addStoreFilter($storeId);

            $this->loadPost($data);
            $this->getConditions()->collectValidatedAttributes($productCollection);
            $this->resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProductConditions']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product'    => $this->_productFactory->create()
                ]
            );
        }

        return $this->productIds;
    }

    /**
     * Callback function for product matching (conditions)
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProductConditions($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        if ($this->getConditions()->validateByEntityId($product->getId())) {
            $this->productIds[] = $product->getId();
        }
    }

    /**
     * Return mapping value by category id
     *
     * @param int $categoryId
     * @return string
     */
    public function getMappingValue($categoryId)
    {
        $result = '';
        $mapping = $this->getAssocMapping();

        if (isset($mapping[$categoryId])) {
            $map = $mapping[$categoryId];
            if ($map['map'] != '') {
                $result = $map['map'];
            } else {
                $path = explode('/', $map['path']);
                $path = array_reverse($path);
                foreach ($path as $id) {
                    if (isset($mapping[$id])) {
                        $parentMap = $mapping[$id];
                        if ($parentMap['map'] != '') {
                            $result = $parentMap['map'];
                            break;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Current mapping as associative array
     *
     * @return array
     */
    public function getAssocMapping()
    {
        if ($this->assocMapping == null) {
            foreach ($this->getMapping() as $map) {
                $this->assocMapping[$map['category_id']] = $map;
            }
        }

        return $this->assocMapping;
    }

    /**
     * Current mapping
     *
     * @return array
     */
    public function getMapping()
    {
        if ($this->mapping == null) {
            $this->buildMapping();
        }

        return $this->mapping;
    }

    /**
     * Build mapping
     *
     * @param int $parentId
     * @return void
     */
    protected function buildMapping($parentId = 0)
    {
        $dataMapping = json_decode($this->getData('mapping_json'), true);

        $collection = $this->categoryFactory->create()->getCollection()->addAttributeToSelect('name')->addAttributeToSelect('children_count')->addAttributeToSelect('level')
            ->addAttributeToFilter('parent_id', $parentId)->setOrder('position', 'asc')->load();

        /** @var Category $category */
        foreach ($collection as $category) {
            $categoryId = $category->getId();
            $map = '';
            $map = isset($dataMapping[$categoryId]) ? $dataMapping[$categoryId] : '';

            if ($category->getName() || $category->getLevel() == 0) {
                $this->mapping[] = [
                    'category_id' => $categoryId,
                    'name' => $category->getName() ? $category->getName() : __('Default Category')->__toString(),
                    'map' => $map,
                    'level' => $category->getLevel(),
                    'path' => $category->getPath(),
                    'parent_id' => $category->getParentId(),
                    'has_childs' => $category->getChildrenCount() ? true : false,
                ];
            }

            if ($category->getChildrenCount()) {
                $this->buildMapping($category->getId());
            }
        }
    }

    /**
     * @return array
     */
    public function getRowsToExport()
    {
        return [
            'name',
            'type',
            'mapping_serialized',
            'mapping'
        ];
    }
    
    public function startEmulation()
    {
        $this->getEmulationInstance()->startEnvironmentEmulation($this->getStoreId(), Area::AREA_FRONTEND, true);

        return true;
    }

    /**
     * @return Emulation
     */
    private function getEmulationInstance()
    {
        if (!$this->_emulationInstance) {
            $this->_emulationInstance = $this->emulationFactory->create();
        }

        return $this->_emulationInstance;
    }

    public function stopEmulation()
    {
        $this->getEmulationInstance()->stopEnvironmentEmulation();

        return true;
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\ProductFeed::class);
    }
}
