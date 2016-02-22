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
$GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['default'] = str_replace(
    'teaser;',
    'teaser,seo_serp_preview;',
    $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['default']
);

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['seo_serp_preview'] = [
    'exclude'   => true,
    'inputType' => 'seoSerpPreview',
    'eval'      => ['engine' => 'Derhaeuptling\SeoSerpPreview\Engine\EventsEngine', 'tl_class' => 'clr'],
];
