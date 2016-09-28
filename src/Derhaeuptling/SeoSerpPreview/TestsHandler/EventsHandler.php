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

class EventsHandler extends AbstractHandler
{
    /**
     * Initialize the tests handler
     *
     * @param DataContainer|null $dc
     */
    public function initialize(DataContainer $dc = null)
    {
        if (CURRENT_ID) {
            $calendar = Database::getInstance()->prepare("SELECT seo_serp_ignore FROM tl_calendar WHERE id=?")
                ->limit(1)
                ->execute(CURRENT_ID);

            // Do not initialize handler if calendar is ignored
            if ($calendar->seo_serp_ignore) {
                unset($GLOBALS['TL_DCA']['tl_calendar_events']['fields']['seo_serp_preview']);

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
        return 'tl_calendar_events';
    }

    /**
     * Get the applicable record IDs
     *
     * @return array
     */
    protected function getRecordIds()
    {
        return Database::getInstance()->prepare("SELECT id FROM tl_calendar_events WHERE pid=?")
            ->execute(CURRENT_ID)
            ->fetchEach('id');
    }
}