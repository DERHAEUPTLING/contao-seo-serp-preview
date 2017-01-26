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
 * Initialize the tests
 */
$GLOBALS['TL_DCA']['tl_article']['config']['onload_callback'][] = [
    'Derhaeuptling\SeoSerpPreview\TestsHandler\ArticleHandler',
    'initialize',
];

$GLOBALS['TL_DCA']['tl_article']['config']['onsubmit_callback'][] = [
    'Derhaeuptling\SeoSerpPreview\StatusManager',
    'rebuildCache',
];

/**
 * Extend palettes
 */
$GLOBALS['TL_DCA']['tl_article']['palettes']['default'] = str_replace(
    'teaser;',
    'teaser,seo_serp_preview;',
    $GLOBALS['TL_DCA']['tl_article']['palettes']['default']
);

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_article']['fields']['seo_serp_preview'] = [
    'exclude'   => true,
    'inputType' => 'seoSerpPreview',
    'eval'      => [
        'engine'   => 'Derhaeuptling\SeoSerpPreview\Engine\ArticleEngine',
        'hidden'   => true,
        'tl_class' => 'clr',
    ],
];
