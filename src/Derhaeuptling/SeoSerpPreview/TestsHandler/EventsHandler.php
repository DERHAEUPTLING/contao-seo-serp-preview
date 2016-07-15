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

class EventsHandler extends AbstractHandler
{
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