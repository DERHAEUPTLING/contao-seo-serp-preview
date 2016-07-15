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

use Derhaeuptling\SeoSerpPreview\Test\Exception\WarningException;
use Derhaeuptling\SeoSerpPreview\Test\Exception\ErrorException;

interface TestInterface
{
    /**
     * Return true if the test supports table
     *
     * @param string $table
     *
     * @return bool
     */
    public function supports($table);

    /**
     * Run the test
     *
     * @param array  $data
     * @param string $table
     *
     * @throws ErrorException
     * @throws WarningException
     */
    public function run(array $data, $table);
}
