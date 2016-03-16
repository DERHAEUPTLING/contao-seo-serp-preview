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
 * Register the namespace
 */
ClassLoader::addNamespace('Derhaeuptling\SeoSerpPreview');

/**
 * Register the classes
 */
ClassLoader::addClasses([
    // Classes
    'Derhaeuptling\SeoSerpPreview\PreviewWidget'                   => 'system/modules/seo_serp_preview/src/PreviewWidget.php',
    'Derhaeuptling\SeoSerpPreview\TestsHandler'                    => 'system/modules/seo_serp_preview/src/TestsHandler.php',
    'Derhaeuptling\SeoSerpPreview\TestsManager'                    => 'system/modules/seo_serp_preview/src/TestsManager.php',

    // Engines
    'Derhaeuptling\SeoSerpPreview\Engine\AbstractEngine'           => 'system/modules/seo_serp_preview/src/Engine/AbstractEngine.php',
    'Derhaeuptling\SeoSerpPreview\Engine\EngineInterface'          => 'system/modules/seo_serp_preview/src/Engine/EngineInterface.php',
    'Derhaeuptling\SeoSerpPreview\Engine\EventsEngine'             => 'system/modules/seo_serp_preview/src/Engine/EventsEngine.php',
    'Derhaeuptling\SeoSerpPreview\Engine\NewsEngine'               => 'system/modules/seo_serp_preview/src/Engine/NewsEngine.php',
    'Derhaeuptling\SeoSerpPreview\Engine\PageEngine'               => 'system/modules/seo_serp_preview/src/Engine/PageEngine.php',

    // Tests
    'Derhaeuptling\SeoSerpPreview\Test\DescriptionTest'            => 'system/modules/seo_serp_preview/src/Test/DescriptionTest.php',
    'Derhaeuptling\SeoSerpPreview\Test\TestInterface'              => 'system/modules/seo_serp_preview/src/Test/TestInterface.php',

    // Test exceptions
    'Derhaeuptling\SeoSerpPreview\Test\Exception\ErrorException'   => 'system/modules/seo_serp_preview/src/Test/Exception/ErrorException.php',
    'Derhaeuptling\SeoSerpPreview\Test\Exception\WarningException' => 'system/modules/seo_serp_preview/src/Test/Exception/WarningException.php',
]);

/**
 * Register the templates
 */
TemplateLoader::addFiles([
    'be_seo_serp_preview' => 'system/modules/seo_serp_preview/templates/backend',
    'be_seo_serp_tests'   => 'system/modules/seo_serp_preview/templates/backend',
]);
