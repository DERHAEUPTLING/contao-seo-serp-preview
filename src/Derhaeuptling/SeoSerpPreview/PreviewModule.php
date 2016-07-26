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
use Contao\BackendModule;
use Contao\Controller;
use Contao\Database;
use Contao\Input;
use Contao\Session;
use Contao\System;
use Database\Result;
use Derhaeuptling\SeoSerpPreview\Test\Exception\ErrorException;
use Derhaeuptling\SeoSerpPreview\Test\Exception\WarningException;
use Derhaeuptling\SeoSerpPreview\Test\TestInterface;
use Derhaeuptling\SeoSerpPreview\TestsHandler\AbstractHandler;

class PreviewModule extends BackendModule
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_seo_serp_module';

    /**
     * Tables to be analyzed
     * @var array
     */
    protected $tables = ['tl_calendar_events', 'tl_news', 'tl_page'];

    /**
     * Redirect URL param name
     * @var string
     */
    protected $redirectParamName = 'seo_serp_redirect';

    /**
     * Generate the module
     *
     * @throws \Exception
     */
    protected function compile()
    {
        if (Input::get($this->redirectParamName)) {
            $this->redirectToModule(Input::get($this->redirectParamName));
        }

        System::loadLanguageFile('seo_serp_module');
        $this->Template->backHref = System::getReferer(true);
        $modules                  = $this->getModules();

        if (count($modules) > 0) {
            $GLOBALS['TL_CSS'][] = 'system/modules/seo_serp_preview/assets/css/module.min.css';
            $GLOBALS['TL_CSS'][] = 'system/modules/seo_serp_preview/assets/css/tests.min.css';

            $this->Template->modules = $this->generateModules($modules);
        }
    }

    /**
     * Redirect the user to given module
     *
     * @param string $target
     */
    protected function redirectToModule($target)
    {
        list ($module, $params) = trimsplit('|', $target);
        $modules = $this->getModules();

        if (!isset($modules[$module])) {
            return;
        }

        $session = Session::getInstance()->getData();

        // Set the filters of all module tables to show all message types
        foreach ($modules[$module]['tables'] as $table) {
            $session['filter'][$table][AbstractHandler::$filterName] = AbstractHandler::getAvailableFilters()[0];
        }

        // Decode the params
        if ($params) {
            $params = '&'.base64_decode($params);
        }

        Session::getInstance()->setData($session);
        Controller::redirect('contao/main.php?do='.$module.$params.'&'.AbstractHandler::$serpParamName.'=1');
    }

    /**
     * Generate the modules
     *
     * @param array $modules
     *
     * @return array
     */
    protected function generateModules(array $modules)
    {
        $return = [];

        foreach ($modules as $name => $module) {
            $tests = [];

            // Run the tests
            foreach ($module['tables'] as $table) {
                $tests = array_merge($tests, $this->runTestsForTable($name, $table));
            }

            $return[] = [
                'name'  => $name,
                'label' => $GLOBALS['TL_LANG']['MOD'][$name][0],
                'url'   => Backend::addToUrl($this->redirectParamName.'='.$name),
                'icon'  => $module['icon'],
                'tests' => $tests,
            ];
        }

        return $return;
    }

    /**
     * Run the tests for given table
     *
     * @param string $module
     * @param string $table
     *
     * @return array
     */
    protected function runTestsForTable($module, $table)
    {
        $db     = Database::getInstance();
        $return = [];

        switch ($table) {
            case 'tl_calendar_events':
                $calendars = $db->execute("SELECT id, title FROM tl_calendar ORDER BY title");

                while ($calendars->next()) {
                    $return[] = [
                        'url'       => Backend::addToUrl(
                            $this->redirectParamName.'='.$module.'|'.base64_encode(
                                'table='.$table.'&id='.$calendars->id
                            )
                        ),
                        'reference' => $calendars->title,
                        'result'    => $this->runTests(
                            $table,
                            $db->prepare("SELECT * FROM tl_calendar_events WHERE pid=?")->execute($calendars->id)
                        ),
                    ];
                }
                break;

            case 'tl_news':
                $archives = $db->execute("SELECT id, title FROM tl_news_archive ORDER BY title");

                while ($archives->next()) {
                    $return[] = [
                        'url'       => Backend::addToUrl(
                            $this->redirectParamName.'='.$module.'|'.base64_encode(
                                'table='.$table.'&id='.$archives->id
                            )
                        ),
                        'reference' => $archives->title,
                        'result'    => $this->runTests(
                            $table,
                            $db->prepare("SELECT * FROM tl_news WHERE pid=?")->execute($archives->id)
                        ),
                    ];
                }
                break;

            case 'tl_page':
                $return[] = [
                    'url'    => Backend::addToUrl($this->redirectParamName.'='.$module),
                    'result' => $this->runTests($table, $db->execute("SELECT * FROM tl_page")),
                ];
                break;
        }

        return $return;
    }

    /**
     * Run the tests on a table
     *
     * @param string $table
     * @param Result $records
     *
     * @return array
     */
    protected function runTests($table, Result $records)
    {
        System::loadLanguageFile('seo_serp_tests');
        $result = ['errors' => 0, 'warnings' => 0];

        /** @var TestInterface $test */
        foreach (TestsManager::getAll() as $test) {
            // Skip the unsupported tests
            if (!$test->supports($table)) {
                continue;
            }

            while ($records->next()) {
                try {
                    $test->run($records->row(), $table);
                } catch (ErrorException $e) {
                    $result['errors']++;
                } catch (WarningException $e) {
                    $result['warnings']++;
                }
            }

            $records->reset();
        }

        return $result;
    }

    /**
     * Get the modules
     *
     * @return array
     */
    protected function getModules()
    {
        $return = [];

        foreach ($GLOBALS['BE_MOD'] as $group => $modules) {
            foreach ($modules as $name => $module) {
                // Skip modules without tables
                if (!is_array($module['tables'])) {
                    continue;
                }

                foreach ($module['tables'] as $table) {
                    if (in_array($table, $this->tables, true)) {
                        $return[$name]['icon']     = $module['icon'] ?: null;
                        $return[$name]['tables'][] = $table;
                    }
                }
            }
        }

        return $return;
    }
}