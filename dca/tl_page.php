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
$GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'][] = [
    'Derhaeuptling\SeoSerpPreview\TestsHandler\PageHandler',
    'initialize',
];

$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = [
    'Derhaeuptling\SeoSerpPreview\StatusManager',
    'rebuildCache',
];

/**
 * Extend palettes
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] = str_replace(
    'description;',
    'description,seo_serp_preview;',
    $GLOBALS['TL_DCA']['tl_page']['palettes']['regular']
);

$GLOBALS['TL_DCA']['tl_page']['palettes']['root'] = str_replace(
    ';{publish_legend',
    ';{seo_serp_legend},seo_serp_ignore;{publish_legend',
    $GLOBALS['TL_DCA']['tl_page']['palettes']['root']
);

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['seo_serp_preview'] = [
    'exclude'   => true,
    'inputType' => 'seoSerpPreview',
    'eval'      => ['engine' => 'Derhaeuptling\SeoSerpPreview\Engine\PageEngine', 'tl_class' => 'clr'],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['seo_serp_ignore'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_page']['seo_serp_ignore'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'clr'],
    'sql'       => "char(1) NOT NULL default ''",
];