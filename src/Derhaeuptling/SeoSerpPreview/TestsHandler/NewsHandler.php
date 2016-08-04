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

class NewsHandler extends AbstractHandler
{
    /**
     * Initialize the tests handler
     *
     * @param DataContainer|null $dc
     */
    public function initialize(DataContainer $dc = null)
    {
        if (CURRENT_ID) {
            $archive = Database::getInstance()->prepare("SELECT seo_serp_ignore FROM tl_news_archive WHERE id=?")
                ->limit(1)
                ->execute(CURRENT_ID);

            // Do not initialize handler if archive is ignored
            if ($archive->seo_serp_ignore) {
                unset($GLOBALS['TL_DCA']['tl_news']['fields']['seo_serp_preview']);

                return;
            }
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
        return 'tl_news';
    }

    /**
     * Get the applicable record IDs
     *
     * @return array
     */
    protected function getRecordIds()
    {
        return Database::getInstance()->prepare("SELECT id FROM tl_news WHERE pid=?")
            ->execute(CURRENT_ID)
            ->fetchEach('id');
    }
}