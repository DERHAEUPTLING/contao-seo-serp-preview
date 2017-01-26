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

namespace Derhaeuptling\SeoSerpPreview\Engine;

use Contao\Controller;
use Contao\Environment;
use Contao\LayoutModel;
use Contao\PageModel;

abstract class AbstractEngine
{
    /**
     * Generate the URL path
     *
     * @param PageModel $pageModel
     *
     * @return string
     */
    protected function generateUrlPath(PageModel $pageModel)
    {
        $pageModel->loadDetails();

        if (($rootModel = PageModel::findByPk($pageModel->rootId)) === null) {
            return '';
        }

        return ($rootModel->rootUseSSL ? 'https://' : 'http://').($rootModel->domain ?: Environment::get('host')).TL_PATH.'/'.$pageModel->alias.'/';
    }

    /**
     * Generate the page title with ##title## as placeholder for dynamic title
     *
     * @param PageModel $pageModel
     *
     * @return string
     */
    protected function generatePageTitle(PageModel $pageModel)
    {
        $pageModel->loadDetails();
        $layoutModel = LayoutModel::findByPk($pageModel->layout);

        if ($layoutModel === null) {
            return '##title##';
        }

        $title = $layoutModel->titleTag ?: '{{page::pageTitle}} - {{page::rootPageTitle}}';
        $title = str_replace('{{page::pageTitle}}', '##title##', $title);

        // Fake the global page object
        $GLOBALS['objPage'] = $pageModel;

        // Replace the insert tags
        $title = Controller::replaceInsertTags($title, false);

        // Remove the faked global page object
        unset($GLOBALS['objPage']);

        return $title;
    }
}
