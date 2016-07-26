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
 * Backend module
 */
$GLOBALS['BE_MOD']['content']['seo_serp_preview'] = [
    'icon'     => 'system/modules/seo_serp_preview/assets/icons/module.png',
    'callback' => 'Derhaeuptling\SeoSerpPreview\PreviewModule',
];

/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['seoSerpPreview'] = 'Derhaeuptling\SeoSerpPreview\PreviewWidget';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['getUserNavigation'][] = ['Derhaeuptling\SeoSerpPreview\StatusManager', 'setMenuStatus'];

/**
 * SEO SERP Tests
 */
\Derhaeuptling\SeoSerpPreview\TestsManager::add('description', 'Derhaeuptling\SeoSerpPreview\Test\DescriptionTest');