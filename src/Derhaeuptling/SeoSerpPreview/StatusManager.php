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

use Contao\Config;
use Contao\Database;
use Derhaeuptling\SeoSerpPreview\Test\Exception\ErrorException;
use Derhaeuptling\SeoSerpPreview\Test\Exception\WarningException;
use Derhaeuptling\SeoSerpPreview\Test\TestInterface;

class StatusManager
{
    /**
     * Cache key
     * @var string
     */
    protected $cacheKey = 'seoSerpCache';

    /**
     * Tables to be analyzed
     * @var array
     */
    protected $tables = ['tl_calendar_events', 'tl_news', 'tl_page'];

    /**
     * Rebuild the cache
     */
    public function rebuildCache()
    {
        Config::persist($this->cacheKey, $this->getStatus());
    }

    /**
     * Get the status
     *
     * @return bool
     */
    public function getStatus()
    {
        foreach ($this->tables as $table) {
            if (!Database::getInstance()->tableExists($table)) {
                continue;
            }

            switch ($table) {
                case 'tl_news':
                    $records = Database::getInstance()->execute(
                        "SELECT * FROM tl_news WHERE pid IN (SELECT id FROM tl_news_archive WHERE seo_serp_ignore='')"
                    );
                    break;

                case 'tl_calendar_events':
                    $records = Database::getInstance()->execute(
                        "SELECT * FROM tl_calendar_events WHERE pid IN (SELECT id FROM tl_calendar WHERE seo_serp_ignore='')"
                    );
                    break;

                default:
                    $records = Database::getInstance()->execute("SELECT * FROM ".$table);
                    break;
            }

            while ($records->next()) {
                try {
                    $this->runTests($table, $records->row());
                } catch (ErrorException $e) {
                    return false;
                } catch (WarningException $e) {
                    // do nothing
                }
            }
        }

        return true;
    }

    /**
     * Run the tests
     *
     * @param string $table
     * @param array  $data
     *
     * @throws ErrorException
     * @throws WarningException
     */
    protected function runTests($table, array $data)
    {
        /** @var TestInterface $test */
        foreach (TestsManager::getAll() as $test) {
            if (!$test->supports($table)) {
                continue;
            }

            $test->run($data, $table);
        }
    }

    /**
     * Set the module status in the menu
     *
     * @param array $modules
     *
     * @return array
     */
    public function setMenuStatus(array $modules)
    {
        if (isset($modules['content']['modules']['seo_serp_preview']) && Config::get($this->cacheKey) === false) {
            $modules['content']['modules']['seo_serp_preview']['label'] .= ' <span class="tl_red">('.$GLOBALS['TL_LANG']['MSC']['seo_serp_status.fixErrors'].')</span>';
        }

        return $modules;
    }
}