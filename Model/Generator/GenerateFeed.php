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

namespace Magenest\ProductFeed\Model\Generator;

use Magenest\ProductFeed\Helper\Data;
use Magenest\ProductFeed\Model\Config\Source\FeedType;
use Magenest\ProductFeed\Model\Generator\Facebook\FacebookGenerator;
use Magenest\ProductFeed\Model\Generator\Google\GoogleGenerator;
use Magenest\ProductFeed\Model\ProductFeed;
use Magenest\ProductFeed\Model\ProductFeedFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;

class GenerateFeed
{
    const TEMPLATE_HEADER = 'header_template';
    const TEMPLATE_ITEMS = 'items_template';
    const TEMPLATE_FOOTER = 'footer_template';
    protected $facebookGenerator;
    protected $googleGenerator;
    protected $_directory;
    protected $fileFactory;
    protected $helperData;
    protected $filesystem;
    protected $modelFactory;
    protected $request;

    /**
     * GenerateFeed constructor.
     *
     * @param FacebookGenerator $facebookGenerator
     * @param GoogleGenerator $googleGenerator
     * @param ProductFeedFactory $modelFactory
     * @param Filesystem\DirectoryList $_directory
     * @param FileFactory $fileFactory
     * @param Data $helperData
     * @param Filesystem $filesystem
     * @param RequestInterface $request
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        FacebookGenerator $facebookGenerator,
        GoogleGenerator $googleGenerator,
        ProductFeedFactory $modelFactory,
        Filesystem\DirectoryList $_directory,
        FileFactory $fileFactory,
        Data $helperData,
        Filesystem $filesystem,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->facebookGenerator = $facebookGenerator;
        $this->googleGenerator = $googleGenerator;
        $this->_directory = $_directory;
        $this->fileFactory = $fileFactory;
        $this->helperData = $helperData;
        $this->modelFactory = $modelFactory;
    }

    /**
     * @param string $type
     * @param ProductFeed $feed
     * @throws LocalizedException
     */
    public function generateOnce($type, $feed)
    {
        switch ($type) {
            case FeedType::GOOGLE_FEED_FILE_TYPE:
                $this->googleGenerator->generateOnce($feed);
                break;
            case FeedType::FACEBOOK_FEED_FILE_TYPE:
                $this->facebookGenerator->generateOnce($feed);
                break;
            default:
                throw new LocalizedException(__("Generate feed type is not supported."));
        }
    }

    public function generateSegment($feed)
    {
        $templateType = $feed->getTypeTemplate();
        $isHeaderGenerated = $feed->getHeaderGenerated();
        if ($templateType == 'google') {
            $lastPercent = $this->generateGoogleSegment($feed);
        } else {
            //generate facebook csv file
            if (!$isHeaderGenerated) {
                $this->facebookGenerator->generateSegment($feed, self::TEMPLATE_HEADER);
                return 10;
            }
            $lastPercent = $this->facebookGenerator->generateSegment($feed, self::TEMPLATE_ITEMS);
        }
        $feed->save();

        return $lastPercent;
    }

    /**
     * @param ProductFeed $feed
     * @return int
     */
    // generate google xml file
    protected function generateGoogleSegment($feed)
    {
        if (!$feed->getHeaderGenerated()) {
            $this->googleGenerator->generateSegment($feed, self::TEMPLATE_HEADER);
            return 10;
        }

        $lastPercent = $this->googleGenerator->generateSegment($feed, self::TEMPLATE_ITEMS);
        if ($lastPercent !== 100) {
            return $lastPercent;
        }
        $this->googleGenerator->generateSegment($feed, self::TEMPLATE_FOOTER);

        return 100;
    }
}
