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

namespace Derhaeuptling\SeoSerpPreview\TestsHandler;

use Contao\Database;
use Contao\DataContainer;

class ArticleHandler extends AbstractHandler
{
    /**
     * Initialize the tests handler
     *
     * @param DataContainer|null $dc
     */
    public function initialize(DataContainer $dc = null)
    {
        $articles = Database::getInstance()->prepare("SELECT id FROM tl_article WHERE showTeaser!=''")
            ->limit(1)
            ->execute();

        // Do not initialize handler if there are no articles to analyze
        if (!$articles->numRows) {
            return;
        }

        parent::initialize($dc);
    }

    /**
     * Get the table name
     *
     * @return string
     */
    protected function getTableName()
    {
        return 'tl_article';
    }

    /**
     * Get the applicable record IDs
     *
     * @return array
     */
    protected function getRecordIds()
    {
        return Database::getInstance()->execute("SELECT id FROM tl_article WHERE showTeaser!=''")->fetchEach('id');
    }

    /**
     * Add the message filter
     */
    protected function addMessageFilter()
    {
        // not supported
    }

    /**
     * Filter the records by message type
     */
    protected function filterRecords()
    {
        // not supported
    }
}
