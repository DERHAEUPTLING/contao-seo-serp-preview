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
use Contao\PageModel;

/**
 * Handle the tl_page table.
 */
class PageEngine extends AbstractEngine implements EngineInterface
{
    /**
     * Get the JavaScript engine name
     *
     * @return string
     */
    public function getJavaScriptEngineName()
    {
        return 'SeoSerpPreview.PageEngine';
    }

    /**
     * Get the JavaScript engine file path
     *
     * @return string
     */
    public function getJavaScriptEngineSource()
    {
        return 'system/modules/seo_serp_preview/assets/js/engine/page.min.js';
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
        if (($pageModel = $this->getPageModel($id)) === null) {
            return '';
        }

        $pageModel->loadDetails();

        if (($rootModel = PageModel::findByPk($pageModel->rootId)) === null) {
            return '';
        }

        return ($rootModel->rootUseSSL ? 'https://' : 'http://').($rootModel->dns ?: Environment::get('host')).TL_PATH.'/';
    }

    /**
     * Get the page title with ##title## as placeholder for dynamic title
     *
     * @param int $id
     *
     * @return string
     */
    public function getPageTitle($id)
    {
        if (($pageModel = $this->getPageModel($id)) === null) {
            return '';
        }

        return $this->generatePageTitle($pageModel);
    }

    /**
     * Get the page model
     *
     * @param int $id
     *
     * @return PageModel|null
     */
    protected function getPageModel($id)
    {
        return PageModel::findByPk($id);
    }
}
