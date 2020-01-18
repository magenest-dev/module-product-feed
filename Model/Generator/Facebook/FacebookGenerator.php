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

namespace Magenest\ProductFeed\Model\Generator\Facebook;

use Exception;
use Liquid\Template;
use Magenest\ProductFeed\Block\Adminhtml\LiquidFilters;
use Magenest\ProductFeed\Helper\Data;
use Magenest\ProductFeed\Helper\Io;
use Magenest\ProductFeed\Model\Generator\Template\GoogleTemplateBuilder;
use Magenest\ProductFeed\Model\Logs;
use Magenest\ProductFeed\Model\ProductFeed;
use Magenest\ProductFeed\Model\ProductFeedFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Serialize\Serializer\Json;

class FacebookGenerator
{
    const FEED_FILE_PATH = BP . '/pub/productfeed/';
    const TEMPLATE_HEADER = 'header_template';
    const TEMPLATE_ITEMS = 'items_template';
    const TEMPLATE_FOOTER = 'footer_template';

    /**
     * @var LiquidFilters
     */
    protected $liquidFilters;

    protected $helper;

    protected $file;

    protected $Io;

    protected $_directory;

    protected $fileFactory;
    protected $helperData;
    protected $filesystem;
    protected $modelFactory;
    protected $csv;
    protected $request;
    protected $logs;

    /** @var Json;
     */
    protected $json;
    /**
     * @var GoogleTemplateBuilder
     */
    protected $tempBuilder;

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
        DirectoryList $_directory,
        LiquidFilters $liquidFilters,
        GoogleTemplateBuilder $tempBuilder,
        ProductFeedFactory $modelFactory,
        FileFactory $fileFactory,
        Data $helperData,
        Filesystem $filesystem,
        Csv $csv,
        RequestInterface $request,
        Logs $logs,
        Json $json
    ) {
        $this->json = $json;
        $this->logs = $logs;
        $this->request = $request;
        $this->filesystem = $filesystem;
        $this->csv = $csv;
        $this->Io = $Io;
        $this->file = $file;
        $this->helper = $helper;
        $this->_directory = $_directory;
        $this->liquidFilters = $liquidFilters;
        $this->tempBuilder = $tempBuilder;
        $this->fileFactory = $fileFactory;
        $this->helperData = $helperData;
        $this->modelFactory = $modelFactory;
        $this->directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::PUB); // Directory Path
    }

    /**
     * @param ProductFeed $feed
     * @throws NoSuchEntityException
     * @throws Exception
     */

    public function generateSegment($feed, $type)
    {
        switch ($type) {
            case self::TEMPLATE_HEADER:
                $generate = $this->generateHeader($feed, $type);
                break;
            case self:: TEMPLATE_ITEMS:
                $generate = $this->generateItem($feed, $type);
                break;
            default:
                throw new LocalizedException(__("Can't generate"));
        }
        return $generate;
//        $this->saveAllFile($feed, $type);
    }

    public function generateOnce($feed)
    {
        $template = new Template();
        $filtersMethods = $this->liquidFilters->getFiltersMethods();
        $template->registerFilter($this->liquidFilters);
        $name = $feed->getFileName();
        $filepath = self::FEED_FILE_PATH . '' . $name;
        $feedData = $feed->getData();
        $attributeTemplate = $this->json->unserialize($feedData['attribute_template']);
        $includeFieldHeader = $attributeTemplate['includefieldheader'];
        $fieldSeparate = $attributeTemplate['fieldseparate'];
        $fieldAroundBy = $attributeTemplate['fieldaroundby'];
        $fieldsMap = $attributeTemplate['fields_map'];
        if($fieldsMap=='') {
            $header = [];
            $this->directory->openFile($filepath, 'w+')->writeCsv($header);
        } else {
            $merge = $this->helperData->processDuplicateDataFacebook($fieldsMap);
            foreach ($merge as $headerData) {
                $header[] = $headerData['col_name'];
                $attributeValue[] = $headerData['col_attr_val'];
                $value[] = $headerData['col_val'];
            }
            $modifyAroundBy = ($fieldAroundBy == "'") ? "+" : $fieldAroundBy;
            $modifySeparate = ($fieldSeparate == ",") ? "~" : $fieldSeparate;
            $row[0] = implode($modifySeparate, $attributeValue);
            $row[1] = implode($modifySeparate, $value);
            $templateHtml = '{% for product in products %}' . $row[1] . $modifySeparate . '{% endfor %}';
            $templateHtml = str_replace('}}', "| mpCorrect:'" . $modifyAroundBy . "','" . $modifySeparate . "'}}", $templateHtml);
            $feed->addData(["check" => "all",]);
            $productCollection = $this->helperData->getProductsData($feed);
            array_push($filtersMethods, 'mpCorrect');
            $template->parse($templateHtml, $filtersMethods);
            $content = $template->render([
                'products' => $productCollection
            ]);
            if($content != '')
            {
                $stream = $this->directory->openFile($filepath, 'w+');
                if ($includeFieldHeader == 0) {
                    $stream->writeCsv($header);
                }
                $data = explode($modifySeparate, $content);
                $headerCount = count($attributeValue);
                while (count($data) >= $headerCount) {
                    $csvData = array_slice($data, 0, $headerCount, true);
                    $stream->writeCsv($csvData);
                    array_splice($data, 0, $headerCount);
                }
            }
            $percent = (count($productCollection->getData()) / count($feed->getMatchingProductIds()));
        }
    }


    // generate header of csv file
    protected function generateHeader($feed, $type)
    {
        $name = 'facebook_feed.csv';
        $filepath = self::FEED_FILE_PATH . '' . self::TEMPLATE_HEADER . '_' . $name; // at Directory path Create a Folder Export and FIle
        $stream = $this->directory->openFile($filepath, 'w+');
        $feedData = $feed->getData();
        $attributeTemplate = $this->json->unserialize($feedData['attribute_template']);
        $includeFieldHeader = $attributeTemplate['includefieldheader'];
        $fieldsMap = $attributeTemplate['fields_map'];
        if($fieldsMap==''){
            $header = [];
        } else {
            $merge = $this->helperData->processDuplicateDataFacebook($fieldsMap);
            foreach ($merge as $headerData) {
                $header[] = $headerData['col_name'];
            }
        }
        if ($includeFieldHeader == 0) {
            $stream->writeCsv($header);
        }
        $this->updateFeedHeader($feed);
//        return 10;
    }
    // set feed header when it is generated
    private function updateFeedHeader(ProductFeed $feed)
    {
        $feed->setHeaderGenerated(true);
        $feed->save();
    }

    /**
     * @param $feed
     * @param $type
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws FileSystemException
     */
    //generate content of csv file
    protected function generateItem($feed, $type)
    {
        $feed->getLastProductId();
        $template = new Template();
        $filtersMethods = $this->liquidFilters->getFiltersMethods();
        $template->registerFilter($this->liquidFilters);
        $name = 'facebook_feed.csv';
        $filepath = self::FEED_FILE_PATH . '' . self::TEMPLATE_ITEMS . '_' . $name; // at Directory path Create a Folder Export and FIle
        $stream = $this->directory->openFile($filepath, 'a');
        $feedData = $feed->getData();
        $attributeTemplate = $this->json->unserialize($feedData['attribute_template']);
        $fieldsMap = $attributeTemplate['fields_map'];
        if ($fieldsMap=='') {
            $csvData = [];
            $stream->writeCsv($csvData);
            $this->saveAllFile($feed, $type, 0);
            $this->clearGenerateData($feed);
            return 100;
        } else {
            $merge = $this->helperData->processDuplicateDataFacebook($fieldsMap);
            foreach ($merge as $field) {
                $attributeValue[] = $field['col_attr_val'];
                $value[] = $field['col_val'];
            }
            $fieldSeparate = $attributeTemplate['fieldseparate'];
            $fieldAroundBy = $attributeTemplate['fieldaroundby'];
            $modifyAroundBy = ($fieldAroundBy == "'") ? "+" : $fieldAroundBy;
            $modifySeparate = ($fieldSeparate == ",") ? "~" : $fieldSeparate;
            $row[0] = implode($modifySeparate, $attributeValue);
            $row[1] = implode($modifySeparate, $value);
            $templateHtml = '{% for product in products %}' . $row[1] . $modifySeparate . '{% endfor %}';
            $templateHtml = str_replace('}}', "| mpCorrect:'" . $modifyAroundBy . "','" . $modifySeparate . "'}}", $templateHtml);

            $productCollection = $this->helperData->getProductsData($feed);
            array_push($filtersMethods, 'mpCorrect');

            $template->parse($templateHtml, $filtersMethods);
            $content = $template->render([
                'products' => $productCollection
            ]);
            $data = explode($modifySeparate, $content);
            $headerCount = count($attributeValue);
            $countData = count($productCollection->getData());
            while (count($data) >= $headerCount) {
                $csvData = array_slice($data, 0, $headerCount, true);
                $stream->writeCsv($csvData);
                array_splice($data, 0, $headerCount);
            }
            $lastPageNum = $productCollection->getLastPageNumber();
            if ($countData < Data::PAGE_SIZE_PRODUCT) {
                $this->saveAllFile($feed, $type, $lastPageNum);
                $this->clearGenerateData($feed);
                return 100;
            }
            if(count($feed->getMatchingProductIds()) == 0) {
                $this->file->rm($filepath);
            } else {
                $percent = ($countData / count($feed->getMatchingProductIds())) * 90;
            }
            return $percent + (float)$this->request->getParam('percent');
        }
    }
    // merge header and content into feed file
    protected function saveAllFile($feed, $type, $lastPageNum)
    {
        $feedData = $feed->getData();
        $attributeTemplate = $this->json->unserialize($feedData['attribute_template']);
        $fieldSeparate = $attributeTemplate['fieldseparate'];
        $fieldAroundBy = $attributeTemplate['fieldaroundby'];
        $name = 'facebook_feed.csv';
        $fileName = $feed->getFileName();
        $filepath = self::FEED_FILE_PATH . '' . $name;
        $headerFile = self::TEMPLATE_HEADER . '_' . $name;
        $itemFile = self::TEMPLATE_ITEMS . '_' . $name;
        $pathHeaderFile = $this->_directory->getPath("pub") . "/productfeed/" . $headerFile;
        $pathItemsFile = $this->_directory->getPath("pub") . "/productfeed/" . $itemFile;
        $temp = '';
        $headerContent = $this->csv->getData($pathHeaderFile);
        $itemsContent = $this->csv->getData($pathItemsFile);
        $temp .= file_get_contents($pathHeaderFile) . '' . file_get_contents($pathItemsFile);
        $data[] = array_merge($headerContent, $itemsContent);
        $fileUrl = self::FEED_FILE_PATH . '' . $fileName;
        $stream = $this->directory->openFile($fileUrl, 'w+');
        foreach ($data as $value) {
            foreach ($value as $result) {
                foreach ($result as $item) {
                    $row[] = $fieldAroundBy . '' . $item . '' . $fieldAroundBy;
                }
                $stream->writeCsv($row, $fieldSeparate);
                $row = null;
            }
        }
        if ($this->file->fileExists($pathHeaderFile) && $this->file->fileExists($pathItemsFile)) {
            $this->file->rm($pathHeaderFile);
            $this->file->rm($pathItemsFile);
        }
        $this->addLogs($feed);
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
