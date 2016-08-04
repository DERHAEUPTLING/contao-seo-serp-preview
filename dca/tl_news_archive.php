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
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] .= ';{seo_serp_legend},seo_serp_ignore';

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_news_archive']['fields']['seo_serp_ignore'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['seo_serp_ignore'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'clr'],
    'sql'       => "char(1) NOT NULL default ''",
];