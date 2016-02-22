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

namespace Derhaeuptling\SeoSerpPreview;

use Contao\Backend;
use Contao\Database;
use Contao\Model\Collection;
use Contao\PageModel;

class TestsModule extends \BackendModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_seo_serp_preview_tests';

    /**
     * Compile the current element
     */
    protected function compile()
    {
        $this->Template->href = Backend::getReferer(true);

        if (($pageModels = $this->fetchPages()) === null) {
            return;
        }

        $this->Template->pages = $this->generatePages($pageModels);
    }

    /**
     * Fetch pages from the database
     *
     * @return \Model\Collection|null
     */
    protected function fetchPages()
    {
        $pageIds = Database::getInstance()->getChildRecords(0, 'tl_page', true);

        if (count($pageIds) === 0) {
            return null;
        }

        return PageModel::findMultipleByIds($pageIds);
    }

    /**
     * Generate the pages
     *
     * @param Collection $pageModels
     *
     * @return array
     */
    protected function generatePages(Collection $pageModels)
    {
        $pages = [];

        /** @var PageModel $pageModel */
        foreach ($pageModels as $pageModel) {
            $pages[$pageModel->id]            = $pageModel->row();
            $pages[$pageModel->id]['title']   = $pageModel->pageTitle ?: $pageModel->title;
            $pages[$pageModel->id]['editUrl'] = sprintf(
                'contao/main.php?do=page&act=edit&id=%s&rt=%s&ref=%s',
                $pageModel->id,
                REQUEST_TOKEN,
                \Input::get('referer')
            );
        }

        return $pages;
    }
}
