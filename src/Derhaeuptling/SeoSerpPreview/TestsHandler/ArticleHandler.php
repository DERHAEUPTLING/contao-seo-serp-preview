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
     * Filter the records by message type
     */
    protected function filterRecords()
    {
        parent::filterRecords();

        $articles = $GLOBALS['TL_DCA'][$this->table]['list']['sorting']['root'];

        if (!is_array($articles) || count($articles) < 1) {
            return;
        }

        if ($articles === [0]) {
            $root = [];
        } else {
            $root = Database::getInstance()->execute(
                "SELECT pid FROM tl_article WHERE id IN (".implode(',', $articles).")"
            )->fetchEach('pid');
        }

        $GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root'] = (count($root) === 0) ? [0] : $root;
    }
}
