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

/**
 * Backend modules
 */
array_insert($GLOBALS['BE_MOD']['design'], 2, [
    'seo_serp_tests' => [
        'callback' => 'Derhaeuptling\SeoSerpPreview\TestsModule',
        'icon'     => 'system/modules/seo_serp_preview/assets/icons/tests.png',
    ]
]);

/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['seoSerpPreview'] = 'Derhaeuptling\SeoSerpPreview\PreviewWidget';
