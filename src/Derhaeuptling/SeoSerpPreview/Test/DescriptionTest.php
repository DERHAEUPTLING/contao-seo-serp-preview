<?php

/**
 * seo_serp_preview extension for Contao Open Source CMS
 *
 * Copyright (C) 2016 derhaeuptling
 *
 * @author  derhaeuptling <https://derhaeuptling.com>
 * @author  Codefog <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace Derhaeuptling\SeoSerpPreview\Test;

use Contao\Date;
use Derhaeuptling\SeoSerpPreview\Test\Exception\ErrorException;
use Derhaeuptling\SeoSerpPreview\Test\Exception\WarningException;

/**
 * Class DescriptionTest
 *
 * Check if the page description is correct.
 */
class DescriptionTest implements TestInterface
{
    const MAX_LENGTH = 156;

    /**
     * Return true if the test supports table
     *
     * @param string $table
     *
     * @return bool
     */
    public function supports($table)
    {
        return in_array($table, ['tl_calendar_events', 'tl_news', 'tl_page'], true);
    }

    /**
     * Run the test
     *
     * @param array  $data
     * @param string $table
     *
     * @throws ErrorException
     * @throws WarningException
     */
    public function run(array $data, $table)
    {
        switch ($table) {
            case 'tl_calendar_events':
            case 'tl_news':
                $this->check($data['teaser']);
                break;

            case 'tl_page':
                $time = Date::floorToMinute();

                if ($data['type'] === 'regular'
                    && $data['robots'] !== 'noindex,nofollow'
                    && $data['published']
                    && (!$data['start'] || $data['start'] <= $time)
                    && (!$data['stop'] || $data['stop'] > $time)
                ) {
                    $this->check($data['description']);
                }
                break;
        }
    }

    /**
     * Run the test
     *
     * @param array  $data
     * @param string $table
     *
     * @throws ErrorException
     * @throws WarningException
     */
    private function check($value)
    {
        // The description does not exist
        if (!$value) {
            throw new ErrorException($GLOBALS['TL_LANG']['SST']['test.description']['empty']);
        }

        // The description is too long
        if (strlen($value) > self::MAX_LENGTH) {
            throw new WarningException(
                sprintf(
                    $GLOBALS['TL_LANG']['SST']['test.description']['length'],
                    self::MAX_LENGTH
                )
            );
        }
    }
}