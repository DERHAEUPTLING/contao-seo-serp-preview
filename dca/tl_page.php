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
 * Extend palettes
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] = str_replace(
    'description;',
    'description,seo_serp_preview;',
    $GLOBALS['TL_DCA']['tl_page']['palettes']['regular']
);

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['seo_serp_preview'] = [
    'exclude'   => true,
    'inputType' => 'seoSerpPreview',
    'eval'      => ['engine' => 'Derhaeuptling\SeoSerpPreview\Engine\PageEngine', 'tl_class' => 'clr'],
];
