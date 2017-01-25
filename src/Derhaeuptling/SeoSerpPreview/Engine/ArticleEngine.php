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

use Contao\ArticleModel;
use Contao\PageModel;

/**
 * Handle the tl_article table.
 */
class ArticleEngine extends AbstractEngine implements EngineInterface
{
    /**
     * Get the JavaScript engine name
     *
     * @return string
     */
    public function getJavaScriptEngineName()
    {
        return 'SeoSerpPreview.ArticleEngine';
    }

    /**
     * Get the JavaScript engine file path
     *
     * @return string
     */
    public function getJavaScriptEngineSource()
    {
        return 'system/modules/seo_serp_preview/assets/js/engine/article.min.js';
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
        if (($articleModel = $this->getArticleModel($id)) === null
            || ($pageModel = $this->getPageModel($articleModel)) === null
        ) {
            return '';
        }

        return $this->generateUrlPath($pageModel).'articles/';
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
        if (($articleModel = $this->getArticleModel($id)) === null
            || ($pageModel = $this->getPageModel($articleModel)) === null
        ) {
            return '';
        }

        return $this->generatePageTitle($pageModel);
    }

    /**
     * Get the message that will be displayed if record is not indexed
     *
     * @return string
     */
    public function getNotIndexedMessage()
    {
        return $GLOBALS['TL_LANG']['MSC']['seo_serp_preview.articleNotIndexed'];
    }

    /**
     * Get the article model
     *
     * @param int $id
     *
     * @return ArticleModel|null
     */
    protected function getArticleModel($id)
    {
        return ArticleModel::findByPk($id);
    }

    /**
     * Get the page model
     *
     * @param ArticleModel $articleModel
     *
     * @return PageModel|null
     */
    protected function getPageModel(ArticleModel $articleModel)
    {
        return PageModel::findByPk($articleModel->pid);
    }
}
