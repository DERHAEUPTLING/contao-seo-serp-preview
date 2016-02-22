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

use Contao\Environment;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\PageModel;

/**
 * Handle the tl_news table.
 */
class NewsEngine implements EngineInterface
{
    /**
     * Get the JavaScript engine name
     *
     * @return string
     */
    public function getJavaScriptEngineName()
    {
        return 'SeoSerpPreview.NewsEngine';
    }

    /**
     * Get the JavaScript engine file path
     *
     * @return string
     */
    public function getJavaScriptEngineSource()
    {
        return 'system/modules/seo_serp_preview/assets/js/engine/news.js';
    }

    /**
     * Get the URL path
     *
     * @param int $id
     *
     * @return string
     */
    public function getUrlPath($id)
    {
        if (($newsModel = NewsModel::findByPk($id)) === null) {
            return '';
        }

        if (($newsArchiveModel = NewsArchiveModel::findByPk($newsModel->pid)) === null) {
            return '';
        }

        if (($pageModel = PageModel::findByPk($newsArchiveModel->jumpTo)) === null) {
            return '';
        }

        $pageModel->loadDetails();

        if (($rootModel = PageModel::findByPk($pageModel->rootId)) === null) {
            return '';
        }

        return ($rootModel->rootUseSSL ? 'https://' : 'http://').($rootModel->domain ?: Environment::get('host')).TL_PATH.'/'.$pageModel->alias.'/';
    }
}
