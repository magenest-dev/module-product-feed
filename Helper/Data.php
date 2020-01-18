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

use DateTime;
use DateTimeZone;
use Magenest\ProductFeed\Model\ProductFeedFactory;
use Magenest\ProductFeed\Model\ResourceModel\ProductFeed;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper as AbstractHelperAlias;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelperAlias
{
    const EMAIL_TEMPLATE_PATH = 'sendmail/mailsetting/template';
    const EMAIL_SENDER_PATH = 'sendmail/mailsetting/identity';
    const EMAIL_SENDTO_PATH = 'sendmail/mailsetting/sendto';
    const FEED_FILE_PATH = BP . '/pub/productfeed/';
    const PAGE_SIZE_PRODUCT = 500;

    /**
     * @var ProductFeedFactory
     */
    protected $feed;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var File
     */
    protected $file;

    protected $productFactory;


    /**
     * @var Resolver
     */
    protected $resolver;
    /**
     *
     * @var  Collection
     */
    protected $productCollection;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $categoryFactory;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistryInterface;

    /** @var UrlInterface */
    protected $_urlBuilder;

    /** @var TimezoneInterface */
    protected $timezone;

    protected $authSession;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var StateInterface */
    protected $inlineTranslation;

    /** @var TransportBuilder */
    protected $transportBuilder;
    protected $escaper;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /** @var Json;
     */
    protected $json;

    protected $stockSaleableInterface;

    /** @var RequestInterface */
    protected $request;

    /** @var Filesystem */
    protected $filesystem;

    private $feedResource;

    protected $generateFeed;

    public function __construct(
        Context $context,
        CollectionFactory $_productCollectionFactory,
        CategoryFactory $categoryFactory,
        Resolver $resolver,
        ProductFeedFactory $feed,
        ProductFactory $productFactory,
        Collection $productCollection,
        StockRegistryInterface $stockRegistryInterface,
        ProductRepositoryInterface $productRepository,
        UrlInterface $_urlBuilder,
        TimezoneInterface $timezone,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        Escaper $escaper,
        Session $authSession,
        File $file,
        RedirectFactory $resultRedirectFactory,
        Json $json,
        ProductFeed $feedResource,
        RequestInterface $request,
        Filesystem $filesystem,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->feedResource = $feedResource;
        $this->_productCollectionFactory = $_productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->feed = $feed;
        $this->stockRegistryInterface = $stockRegistryInterface;
        $this->categoryFactory = $categoryFactory;
        $this->resolver = $resolver;
        $this->productCollection = $productCollection;
        $this->file = $file;
        $this->productFactory = $productFactory;
        $this->authSession = $authSession;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->_urlBuilder = $_urlBuilder;
        $this->timezone = $timezone;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->json = $json;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    // send mail
    public function sendMail($generateMessage)
    {
        $vars = [
            'generateMessage' => $generateMessage
        ];
        $mailTemplate = $this->scopeConfig->getValue('sendmail/mailsetting/template');
        $this->inlineTranslation->suspend();
        try {
            $transport = $this->transportBuilder->setTemplateIdentifier($mailTemplate == null ? "sendmail_mailsetting_template" : $mailTemplate)->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ]
            )->setTemplateVars($vars)->setFrom($this->getSender())->addTo(
                $this->getRecipient()
            )->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (MailException $e) {
            $this->_logger->critical($e->getMessage());
        }
    }

    public function getSender()
    {
        return $this->scopeConfig->getValue(self::EMAIL_SENDER_PATH) ?: 'sales';
    }

    public function getRecipient()
    {
        return $this->scopeConfig->getValue(self::EMAIL_SENDTO_PATH);
    }

    /**
     * @param \Magenest\ProductFeed\Model\ProductFeed $feed
     * @return Collection
     * @throws NoSuchEntityException
     */
    // set data before generate
    public function getProductsData($feed)
    {
        $categoryMap = $this->unserialize($feed->getMappingJson());
        $allCategory = $this->categoryFactory->create()->getCollection()->addAttributeToSelect('name');
        $categoriesName = [];
        /** @var $item Category */
        foreach ($allCategory as $item) {
            $categoriesName[$item->getId()] = $item->setStoreId($feed->getStoreId())->getName();
        }
        /**
         * @todo Need check if feed has product filter, if not ignore getMatchingProductIds operation
         */
        $checkCondition = $this->unserialize($feed->getConditionsSerialized());
        if(isset($checkCondition['conditions'])) {
            if (!$feed->getMatchingProductId()) {
                $saveMatchingProductId = "";
                $matchingProductIds = $feed->getMatchingProductIds();
                $saveMatchingProductId = $this->json->serialize($matchingProductIds);
                $feed->setMatchingProductId($saveMatchingProductId);
            } else {
                $matchingProductIds = $this->json->unserialize($feed->getMatchingProductId());
            }
        }
        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addOrder('entity_id', 'ASC');
        $lastProductId = $feed->getLastProductId();
        if (isset($lastProductId) && !empty($lastProductId)) {
            $productCollection->addFieldToFilter('entity_id', ["gt" => $lastProductId]);
        }
        if($feed->getCheck()!="all") {
            $productCollection->setPageSize(self::PAGE_SIZE_PRODUCT)->setCurPage(1);
        }
        $productCollection->addAttributeToSelect('*')->addStoreFilter($feed->getStoreId());
        if (isset($matchingProductIds)) {
            $productCollection->addIdFilter($matchingProductIds);
        }
        $productCollection->addMediaGalleryData();

        $saveProductId = 0;
        /** @var $product Product */
        foreach ($productCollection as $product) {
            $typeInstance = $product->getTypeInstance();
            $childProductCollection = $typeInstance->getAssociatedProducts($product);
            if ($childProductCollection) {
                $product->setAssociatedProducts($childProductCollection);
            } else {
                $product->setAssociatedProducts([]);
            }

            $product->getCategory();

            $qty = $this->stockRegistryInterface->getStockItemBySku($product->getSku())->getQty();
            $categories = $product->getCategoryCollection()->addAttributeToSelect('*');


            $relatedProducts = $product->getRelatedProducts();
            $crossSellProducts = $product->getCrossSellProducts();
            $upSellProducts = $product->getUpSellProducts();
            $finalPrice = $product->getFinalPrice();
            $productLink = $product->getUrlModel()->getUrlInStore($product, ['_escape' => true]);
            $imageLink = $this->storeManager->getStore($feed->getStoreId())->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
            $images = $product->getMediaGalleryImages();
            /** @var $category Category */
            $cat = false;
            $lv = 0;
            foreach ($categories as $category) {
                if ($lv < $category->getLevel()) {
                    $lv = $category->getLevel();
                    $cat = $category;
                }
            }
            $mapping = '';
            $categoryPath = '';
            if ($cat && $cat instanceof Category && isset($categoryMap[$cat->getId()])) {
                if ($feed->getTypeTemplate() == 'facebook' && $categoryMap[$cat->getId()] == '') {
                    $mapping = $categoriesName[$cat->getId()];
                } else {
                    $mapping = $categoryMap[$cat->getId()];
                }
                $catPaths = array_reverse(explode(',', $cat->getPathInStore()));
                foreach ($catPaths as $index => $catId) {
                    if ($index == (count($catPaths) - 1)) {
                        $categoryPath .= $categoriesName[$catId];
                    } else {
                        $categoryPath .= $categoriesName[$catId] . ' > ';
                    }
                }
            }
            $product->setData('category_path', $categoryPath);
            $product->setData('mapping', $mapping);
            $product->isAvailable() ? $product->setData('quantity_and_stock_status', 'in stock') : $product->setData('quantity_and_stock_status', 'out of stock');
            $product->setData('categoryCollection', $categories);
            $product->setData('relatedProducts', $relatedProducts);
            $product->setData('crossSellProducts', $crossSellProducts);
            $product->setData('upSellProducts', $upSellProducts);
            $product->setData('finalPrice', $finalPrice);
            $product->setData('link', $productLink);
            $product->setData('image_link', $imageLink);
            $product->setData('images', $images);
            $product->setData('qty', $qty);
            $saveProductId = $product->getId();
        }
        $feed->setLastProductId($saveProductId);

        return $productCollection;
    }

    public function processDuplicateDataFacebook($fieldsMap)
    {
        $merge = [];
        foreach ($fieldsMap as $v) {
            if(!isset($merge[$v["col_name"]])) {
                $merge[$v['col_name']]['col_name'] = $v['col_name'];
                $merge[$v['col_name']]['col_type'] = $v['col_type'];
                $merge[$v['col_name']]['col_attr_val'] = $v['col_attr_val'];
                $merge[$v['col_name']]['col_pattern_val'] = $v['col_pattern_val'];
                $merge[$v['col_name']]['col_val'] = $v['col_val'];
            } else {
                $merge[$v['col_name']]['col_val'] .=" ".$v['col_val'];
            }
        }
        return $merge;
    }

    public function getTemplateFacebook($feedData)
    {
        $attributeTemplate = $this->json->unserialize($feedData['attribute_template']);
        $fieldSeparate = $attributeTemplate['fieldseparate'];
        $fieldAroundBy = $attributeTemplate['fieldaroundby'];
        $fieldsMap = $attributeTemplate['fields_map'];
        $merge = $this->processDuplicateDataFacebook($fieldsMap);
        foreach ($merge as $field) {
            $attributeValue[] = $field['col_attr_val'];
            $value[] = $field['col_val'];
        }
        $modifyAroundBy = ($fieldAroundBy == "'") ? "+" : $fieldAroundBy;
        $modifySeparate = ($fieldSeparate == ",") ? "~" : $fieldSeparate;
        $row[0] = implode($modifySeparate, $attributeValue);
        $row[1] = implode($modifySeparate, $value);
        $templateHtml = '{% for product in products %}' . $row[1] . $modifySeparate . '{% endfor %}';
        $templateHtml = str_replace('}}', "| mpCorrect:'" . $modifyAroundBy . "','" . $modifySeparate . "'}}", $templateHtml);
        $array = [$templateHtml,$modifySeparate,$attributeValue];
        return $array;
    }

    public function unserialize($string)
    {
        if (!$this->isJson($string)) {

            return is_array($string) ?: [$string];
        }

        return $this->json->unserialize($string);
    }

    public function isJson($string)
    {
        if (!empty($string)) {
            json_decode($string);

            return (json_last_error() == JSON_ERROR_NONE);
        }

        return false;
    }
    // get template of file facebook or google
    public function getTemplate()
    {
        $id = $this->request->getParam('feed_id');
        $feed = $this->feed->create()->load($id);
        $templateType = $feed->getTypeTemplate();
        return $templateType;
    }

    /**
     * @param $id
     * @return array
     * @throws NoSuchEntityException
     */
    public function getStoreData($id)
    {
        $store = $this->storeManager->getStore($id);
        $locale = $this->resolver->getLocale();

        return [
            'locale_code' => $locale,
            'base_url' => $store->getBaseUrl()
        ];
    }

    public function serialize($string)
    {
        return $this->json->serialize($string);
    }

    public function getUrlFile($fileName)
    {

        return $this->_urlBuilder->getBaseUrl() . 'pub/productfeed/' . $fileName;
    }

    /**
     * @param $time
     * @return DateTime|string
     */
    public function convertTime($time)
    {
        $localTime = new DateTime($time, new DateTimeZone('UTC'));
        $localTime->setTimezone(new DateTimeZone($this->timezone->getConfigTimezone()));
        $localTime = $localTime->format('Y-m-d H:i:s');

        return $localTime;
    }
}
