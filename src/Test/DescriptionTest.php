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
     * Run the test
     *
     * @param array $data
     *
     * @throws ErrorException
     * @throws WarningException
     */
    public function run(array $data)
    {
        if ($data['type'] !== 'regular') {
            return;
        }

        // The description does not exist
        if (!$data['description']) {
            throw new ErrorException($GLOBALS['TL_LANG']['SST']['test.description']['empty']);
        }

        // The description is too long
        if (strlen($data['description']) > self::MAX_LENGTH) {
            throw new WarningException(sprintf(
                $GLOBALS['TL_LANG']['SST']['test.description']['length'],
                self::MAX_LENGTH
            ));
        }
    }
}
