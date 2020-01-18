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

namespace Magenest\ProductFeed\Cron;

use Exception;
use Magenest\ProductFeed\Helper\Data;
use Magenest\ProductFeed\Model\Config\Source\FeedType;
use Magenest\ProductFeed\Model\ResourceModel\ProductFeed\Collection;
use Magenest\ProductFeed\Model\ResourceModel\ProductFeed\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class GenerateFeed
{
    protected $generateFeed;

    protected $logger;

    protected $feedCollection;

    protected $helperData;

    protected $scopeConfig;

    /**
     * GenerateFeed constructor.
     *
     * @param \Magenest\ProductFeed\Model\Generator\GenerateFeed $generateFeed
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magenest\ProductFeed\Model\Generator\GenerateFeed $generateFeed,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        Data $helperData,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
        $this->feedCollection = $collectionFactory;
        $this->logger = $logger;
        $this->generateFeed = $generateFeed;
    }

    public function generate()
    {
        $generateCollection = $this->geeFeedGenerationCollection();
        $status = $this->scopeConfig->getValue('sendmail/mailsetting/status');
        $when = $this->scopeConfig->getValue('sendmail/mailsetting/when');
        foreach ($generateCollection as $feed) {
            try {
                if ($feed->getTypeTemplate() == 'facebook') {
                    $this->generateFeed->generateOnce(FeedType::FACEBOOK_FEED_FILE_TYPE, $feed);
                } else {
                    $this->generateFeed->generateOnce(FeedType::GOOGLE_FEED_FILE_TYPE, $feed);
                }
                $this->logger->critical("Feed Generation: Success");
                if($status==1 && in_array("1", explode(",",$when))) {
                    $this->helperData->sendMail("success");
                }
            } catch (Exception $e) {
                if($status==1 && in_array("0", explode(",",$when))) {
                    $this->helperData->sendMail("fail");
                }
                $this->logger->critical("Feed Generation: Error" . $e->getMessage());
            }
        }
        return true;
    }

    protected function geeFeedGenerationCollection()
    {
        /** @var Collection $collection */
        $collection = $this->feedCollection->create();
        $collection->addFieldToFilter('status', '1');

        return $collection;
    }
}
