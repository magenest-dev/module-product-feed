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

namespace Magenest\ProductFeed\Model\Config\Source;

class FeedType extends AbstractSource
{
    const GOOGLE_FEED_FILE_TYPE = 'google';
    const FACEBOOK_FEED_FILE_TYPE = 'facebook';

    public static function getAllOptions()
    {
        return [
            self::GOOGLE_FEED_FILE_TYPE => "Google Shopping"
        ];
    }
}
