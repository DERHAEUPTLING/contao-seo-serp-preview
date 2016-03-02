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
}
