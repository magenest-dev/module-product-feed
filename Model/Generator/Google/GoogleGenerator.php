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

namespace Magenest\ProductFeed\Model\Generator\Google;

use Exception;
use Liquid\Template;
use Magenest\ProductFeed\Block\Adminhtml\LiquidFilters;
use Magenest\ProductFeed\Helper\Data;
use Magenest\ProductFeed\Helper\Io;
use Magenest\ProductFeed\Model\Generator\Template\GoogleTemplateBuilder;
use Magenest\ProductFeed\Model\ProductFeed;
use Magenest\ProductFeed\Model\Logs;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\RequestInterface;

class GoogleGenerator
{
    const FEED_FILE_PATH = BP . '/pub/productfeed/';
    const TEMPLATE_HEADER = 'header_template';
    const TEMPLATE_ITEMS = 'items_template';
    const TEMPLATE_FOOTER = 'footer_template';
    const NOTIFICATION= '1';

    /**
     * @var LiquidFilters
     */
    protected $liquidFilters;

    protected $helper;

    protected $file;

    protected $Io;

    protected $_directory;

    /**
     * @var GoogleTemplateBuilder
     */
    protected $tempBuilder;

    /** @var Filesystem */
    protected $filesystem;

    /** @var RequestInterface */
    protected $request;
    protected $logs;
    /**
     * GenerateFeed constructor.
     *
     * @param LiquidFilters $liquidFilters
     * @param Data $helper
     * @param File $file
     */
    public function __construct(
        Io $Io,
        Data $helper,
        File $file,
        RequestInterface $request,
        DirectoryList $_directory,
        Filesystem $filesystem,
        LiquidFilters $liquidFilters,
        GoogleTemplateBuilder $tempBuilder,
        Logs $logs
    ) {
        $this->Io = $Io;
        $this->file = $file;
        $this->helper = $helper;
        $this->request = $request;
        $this->_directory = $_directory;
        $this->filesystem = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::PUB);
        $this->logs = $logs;
        $this->liquidFilters = $liquidFilters;
        $this->tempBuilder = $tempBuilder;
    }

    /**
     * @param ProductFeed $feed
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function generateOnce($feed)
    {
        $template = new Template;
        $filtersMethods = $this->liquidFilters->getFiltersMethods();
        $template->registerFilter($this->liquidFilters);
        $templateContent = "";
        $typesTemplate = [self::TEMPLATE_HEADER, self:: TEMPLATE_ITEMS, self:: TEMPLATE_FOOTER];
        foreach ($typesTemplate as $type) {
            $templateContent .= $this->tempBuilder->buildTemplate($feed, $type);
        }
        $fileType = $feed->getFiletype();
        $feed->startEmulation();
        $feed->addData(["check" => "all",]);
        $productCollection = $this->helper->getProductsData($feed);
        $template->parse($templateContent, $filtersMethods);
        $content = $template->render(
            [
                'products' => $productCollection,
                'store' => $this->helper->getStoreData($feed->getStoreId())
            ]
        );
        if(count($feed->getMatchingProductIds()) != 0)
        {
            $this->file->checkAndCreateFolder(self::FEED_FILE_PATH);
            $fileName = $feed->getFilename();
            $fileUrl = self::FEED_FILE_PATH . '' . $fileName;
            $this->file->write($fileUrl, $content);
        }
        $feed->stopEmulation();
        $percent = (count($productCollection->getData()) / count($feed->getMatchingProductIds()));
    }

    public function generateSegment($feed, $type)
    {
        switch ($type) {
            case self::TEMPLATE_HEADER:
                $generate = $this->generateHeader($feed, $type);
                break;
            case self:: TEMPLATE_ITEMS:
                $generate = $this->generateItem($feed, $type);
                break;
            case self:: TEMPLATE_FOOTER:
                $generate = $this->generateFooter($feed, $type);
                break;
            default:
                throw new LocalizedException(__("Can't generate"));

        }
        return $generate;
    }
    // generate header google xml file
    protected function generateHeader($feed, $type)
    {

        $template = new Template();
        $tempHeaderContent = $this->tempBuilder->buildTemplate($feed, $type);
        $template->parse($tempHeaderContent);
        $feed->startEmulation();
        $parseHeader = $template->render([
            'store' => $this->helper->getStoreData($feed->getStoreId())
        ]);

        $fileName = $feed->getFilename();
        $fileUrl = self::FEED_FILE_PATH . '' . self::TEMPLATE_HEADER . '_' . $fileName;
        $this->file->write($fileUrl, $parseHeader);
        $feed->setHeaderGenerated(1);
        $feed->stopEmulation();
    }
    //generate content google xml file
    protected function generateItem($feed, $type)
    {
        $template = new Template();
        $filtersMethods = $this->liquidFilters->getFiltersMethods();
        $template->registerFilter($this->liquidFilters);
        $tempItem = $this->tempBuilder->buildTemplate($feed, $type);
        $feed->startEmulation();
        $productCollection = $this->helper->getProductsData($feed);
        $template->parse($tempItem, $filtersMethods);
        $parseItem = $template->render([
            'products' => $productCollection
        ]);
        $countData =   count($productCollection->getData());
        $fileName = $feed->getFilename();
        $fileUrl = self::FEED_FILE_PATH . '' . self::TEMPLATE_ITEMS . '_' . $fileName;
        $this->filesystem->openFile($fileUrl, 'a')->write($parseItem);
        $feed->stopEmulation();
        $lastPercent = ($countData / count($feed->getMatchingProductIds())) * 90;
        $this->updateFeedData($feed);
        if ($countData < Data::PAGE_SIZE_PRODUCT) {
            return  100;
        }
        return (int)$lastPercent + $this->request->getParam('percent');
    }
    // save last product id from Helper/Data.php
    private function updateFeedData($feed)
    {
        $feed->save();
    }

    // generate footer xml file
    protected function generateFooter($feed, $type)
    {
        $template = new Template();
        $tempFooter = $this->tempBuilder->buildTemplate($feed, $type);
        $template->parse($tempFooter);
        $parseFooter = $template->render([
            "</channel></rss>"
        ]);
        $fileName = $feed->getFilename();
        $fileUrl = self::FEED_FILE_PATH . '' . self::TEMPLATE_FOOTER . '_' . $fileName;
        $this->file->write($fileUrl, $parseFooter);
        $feed->setFooterGenerated(1);
        $this->clearGenerateData($feed);
        $this->saveAllFile($feed);
    }

    // merge header,content,footer to feed file
    protected function saveAllFile($feed)
    {
        $fileName = $feed->getFilename();
        $headerFile = self::TEMPLATE_HEADER . '_' . $fileName;
        $itemFile = self::TEMPLATE_ITEMS . '_' . $fileName;
        $footerFile = self::TEMPLATE_FOOTER . '_' . $fileName;
        $pathHeaderFile = $this->_directory->getPath("pub") . "/productfeed/" . $headerFile;
        $pathItemsFile = $this->_directory->getPath("pub") . "/productfeed/" . $itemFile;
        $pathFooterFile = $this->_directory->getPath("pub") . "/productfeed/" . $footerFile;
        $temp = '';
        $temp .= file_get_contents($pathHeaderFile) . '' . file_get_contents($pathItemsFile) . '' . file_get_contents($pathFooterFile);
        $fileUrl = self::FEED_FILE_PATH . '' . $fileName;
        $this->file->write($fileUrl, $temp, 'w');
        $this->removeFile($pathHeaderFile, $pathItemsFile, $pathFooterFile);
        $this->addLogs($feed);
    }

    // remove header,content,footer when main xml file generated
    private function removeFile($pathHeaderFile, $pathItemsFile, $pathFooterFile)
    {
        $existsHeader = $this->file->fileExists($pathHeaderFile);
        $existsItems = $this->file->fileExists($pathItemsFile);
        $existsFooter = $this->file->fileExists($pathFooterFile);
        if ($existsHeader && $existsItems && $existsFooter) {
            $this->file->rm($pathHeaderFile);
            $this->file->rm($pathItemsFile);
            $this->file->rm($pathFooterFile);
        }
    }

    public function addLogs($feed)
    {
        $logs = $this->logs->addData([
            "feed_id" => $feed->getId(),
            "file_name" => $feed->getFileName(),
            "feed_status" => $feed->getStatus(),
            "create_at" => date("Y-m-d H:i:s", time())
        ]);
        $logs->save();
    }
    /**
     * @param ProductFeed $feed
     */
    //clear data when file is generated
    public function clearGenerateData($feed)
    {
        $feed->addData([
            "header_generated" => null,
            "footer_generated" => null,
            "last_product_id" => null,
            "is_generation_processing" => null,
            "matching_product_id" => null,
            'updated_at' => date("Y-m-d H:i:s", time())
        ]);
        $feed->save();
    }
}
