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
use Contao\BackendUser;
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
            $this->redirectToModule(Input::get($this->redirectParamName, true));
        }

        System::loadLanguageFile('seo_serp_module');
        $this->Template->backHref = System::getReferer(true);
        $modules                  = $this->getModules();

        if (count($modules) > 0) {
            $GLOBALS['TL_CSS'][] = 'system/modules/seo_serp_preview/assets/css/module.min.css';
            $GLOBALS['TL_CSS'][] = 'system/modules/seo_serp_preview/assets/css/tests.min.css';

            $this->Template->modules = $this->generateModules($modules);
        }

        // Rebuild the cache in status manager
        $statusManager = new StatusManager();
        $statusManager->rebuildCache();
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

            // Skip modules without tests
            if (count($tests) < 1) {
                continue;
            }

            $success = true;

            // Check if there is a test that failed
            foreach ($tests as $test) {
                if ($test['result']['errors'] > 0 || $test['result']['warnings'] > 0) {
                    $success = false;
                    break;
                }
            }

            $return[] = [
                'name'      => $name,
                'label'     => $GLOBALS['TL_LANG']['MOD'][$name][0],
                'url'       => Backend::addToUrl($this->redirectParamName.'='.$name),
                'icon'      => $module['icon'],
                'hasAccess' => $this->checkModulePermission($name),
                'tests'     => $tests,
                'success'   => $success,
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
        $user   = BackendUser::getInstance();
        $return = [];

        switch ($table) {
            case 'tl_calendar_events':
                $calendars = $db->execute("SELECT id, title FROM tl_calendar WHERE seo_serp_ignore='' ORDER BY title");

                while ($calendars->next()) {
                    $test = $this->runTests(
                        $table,
                        $db->prepare("SELECT * FROM tl_calendar_events WHERE pid=?")->execute($calendars->id)
                    );

                    $return[] = [
                        'url'       => Backend::addToUrl(
                            $this->redirectParamName.'='.$module.'|'.base64_encode(
                                'table='.$table.'&id='.$calendars->id
                            )
                        ),
                        'reference' => $calendars->title,
                        'message'   => $this->generateTestMessage($test),
                        'result'    => $test,
                        'hasAccess' => $user->isAdmin || (is_array($user->calendars) && in_array(
                                    $calendars->id,
                                    $user->calendars
                                )),
                    ];
                }
                break;

            case 'tl_news':
                $archives = $db->execute(
                    "SELECT id, title FROM tl_news_archive WHERE seo_serp_ignore='' ORDER BY title"
                );

                while ($archives->next()) {
                    $test = $this->runTests(
                        $table,
                        $db->prepare("SELECT * FROM tl_news WHERE pid=?")->execute($archives->id)
                    );

                    $return[] = [
                        'url'       => Backend::addToUrl(
                            $this->redirectParamName.'='.$module.'|'.base64_encode(
                                'table='.$table.'&id='.$archives->id
                            )
                        ),
                        'reference' => $archives->title,
                        'message'   => $this->generateTestMessage($test),
                        'result'    => $test,
                        'hasAccess' => $user->isAdmin || (is_array($user->news) && in_array(
                                    $archives->id,
                                    $user->news
                                )),
                    ];
                }
                break;

            case 'tl_page':
                $pages = $db->execute("SELECT * FROM tl_page");

                if ($pages->numRows) {
                    $notes = [];
                    $test  = $this->runTests($table, $pages->reset());

                    // Add the note if the user is not admin and there are some errors or warnings
                    if (!$user->isAdmin && ($test['errors'] > 0 || $test['warnings'] > 0)) {
                        $rootCount = 0;
                        $userCount = 0;

                        // Count the total root pages and thoes the user has access to
                        while ($pages->next()) {
                            if ($pages->type === 'root') {
                                $rootCount++;

                                if (in_array($pages->id, (array)$user->pagemounts)) {
                                    $userCount++;
                                }
                            }
                        }

                        if ($userCount < $rootCount) {
                            $notes[] = $GLOBALS['TL_LANG']['MSC']['seo_serp_module.pagesNote'];
                        }
                    }

                    $return[] = [
                        'url'       => Backend::addToUrl($this->redirectParamName.'='.$module),
                        'message'   => $this->generateTestMessage($test),
                        'result'    => $test,
                        'hasAccess' => true,
                        'notes'     => $notes,
                    ];
                }
                break;
        }

        return $return;
    }

    /**
     * Generate the test message
     *
     * @param array $test
     *
     * @return string
     */
    protected function generateTestMessage(array $test)
    {
        $message  = '';
        $errors   = $test['errors'];
        $warnings = $test['warnings'];

        // No errors, no warnings
        if ($errors === 0 && $warnings === 0) {
            $message = $GLOBALS['TL_LANG']['MSC']['seo_serp_module.clear'];
        }

        // There are errors
        if ($errors > 0) {
            $message = sprintf(
                '%s %s',
                ($errors === 1) ? $GLOBALS['TL_LANG']['MSC']['seo_serp_module.single'] : $GLOBALS['TL_LANG']['MSC']['seo_serp_module.multiple'],
                sprintf(
                    ($errors === 1) ? $GLOBALS['TL_LANG']['MSC']['seo_serp_module.error'] : $GLOBALS['TL_LANG']['MSC']['seo_serp_module.errors'],
                    $errors
                )
            );
        }

        // There are warnings
        if ($warnings > 0) {
            $chunk = sprintf(
                ($warnings === 1) ? $GLOBALS['TL_LANG']['MSC']['seo_serp_module.warning'] : $GLOBALS['TL_LANG']['MSC']['seo_serp_module.warnings'],
                $warnings
            );

            // Append text to the existing message
            if (strlen($message) > 0) {
                $message = sprintf(
                    '%s %s %s',
                    $message,
                    $GLOBALS['TL_LANG']['MSC']['seo_serp_module.and'],
                    $chunk
                );
            } else {
                $message = sprintf(
                    '%s %s',
                    ($warnings === 1) ? $GLOBALS['TL_LANG']['MSC']['seo_serp_module.single'] : $GLOBALS['TL_LANG']['MSC']['seo_serp_module.multiple'],
                    $chunk
                );
            }
        }

        return $message.'.';
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

    /**
     * Check the module permission
     *
     * @param string $module
     *
     * @return bool
     */
    protected function checkModulePermission($module)
    {
        $user = BackendUser::getInstance();

        if ($user->isAdmin) {
            return true;
        }

        return $user->hasAccess($module, 'modules');
    }
}