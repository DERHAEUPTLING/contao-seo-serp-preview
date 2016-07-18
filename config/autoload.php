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
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('Derhaeuptling\SeoSerpPreview', 'system/modules/seo_serp_preview/src');

/**
 * Register the templates
 */
TemplateLoader::addFiles([
    'be_seo_serp_module'  => 'system/modules/seo_serp_preview/templates/backend',
    'be_seo_serp_preview' => 'system/modules/seo_serp_preview/templates/backend',
    'be_seo_serp_tests'   => 'system/modules/seo_serp_preview/templates/backend',
]);
