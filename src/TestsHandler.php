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
use Contao\BackendTemplate;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\Session;
use Contao\System;
use Derhaeuptling\SeoSerpPreview\Test\Exception\ErrorException;
use Derhaeuptling\SeoSerpPreview\Test\Exception\WarningException;
use Derhaeuptling\SeoSerpPreview\Test\TestInterface;

class TestsHandler
{
    /**
     * Filter name
     * @var string
     */
    protected $filterName = 'seo_serp_tests';

    /**
     * Initialize the tests handler
     *
     * @param DataContainer|null $dc
     */
    public function initialize(DataContainer $dc = null)
    {
        if ($dc === null || $dc->id) {
            return;
        }

        $this->addGlobalOperations();

        if ($this->isEnabled()) {
            $this->autoExpandTree();
            $this->addMessageFilter();
            $this->filterRecords();
            $this->replaceLabelGenerator();
        }
    }

    /**
     * Auto expand the tree
     *
     * @throws \Exception
     */
    public function autoExpandTree()
    {
        $session = Session::getInstance()->getData();

        if ($session['seo_serp_expand_tree'] !== 'tl_page') {
            return;
        }

        $nodes = Database::getInstance()->execute("SELECT DISTINCT pid FROM tl_page WHERE pid>0");

        // Reset the array first
        $session['tl_page_tree'] = [];

        // Expand the tree
        while ($nodes->next()) {
            $session['tl_page_tree'][$nodes->pid] = 1;
        }

        // Avoid redirect loop
        $session['seo_serp_expand_tree'] = null;

        Session::getInstance()->setData($session);
        Backend::reload();
    }

    /**
     * Return true if the tests are enabled
     *
     * @return bool
     */
    protected function isEnabled()
    {
        return \Input::get('serp_tests') ? true : false;
    }

    /**
     * Add the message filter
     */
    protected function addMessageFilter()
    {
        $GLOBALS['TL_DCA']['tl_page']['list']['sorting']['panelLayout'] = 'serp_message_filter,'.$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['panelLayout'];

        $GLOBALS['TL_DCA']['tl_page']['list']['sorting']['panel_callback']['serp_message_filter'] = [
            'Derhaeuptling\SeoSerpPreview\TestsHandler',
            'generateMessageFilter',
        ];
    }

    /**
     * Generate the message filter
     *
     * @return string
     */
    public function generateMessageFilter()
    {
        $filter  = 'tl_page';
        $session = Session::getInstance()->getData();

        // Set filter from user input
        if (Input::post('FORM_SUBMIT') === 'tl_filters') {
            if (Input::post($this->filterName, true) !== 'tl_'.$this->filterName) {
                $session['filter'][$filter][$this->filterName] = \Input::post($this->filterName, true);
                $session['seo_serp_expand_tree']               = 'tl_page';
            } else {
                unset($session['filter'][$filter][$this->filterName]);
            }

            Session::getInstance()->setData($session);
        }

        $return = '<div class="tl_filter tl_subpanel">
<strong>'.$GLOBALS['TL_LANG']['MSC']['seo_serp_tests.filter'][0].'</strong>
<select name="seo_serp_tests" class="tl_select'.(isset($session['filter'][$filter][$this->filterName]) ? ' active' : '').'">
<option value="tl_seo_serp_tests">'.$GLOBALS['TL_LANG']['MSC']['seo_serp_tests.filter'][1].'</option>
<option value="tl_seo_serp_tests">---</option>';

        foreach ($this->getAvailableFilters() as $option) {
            $selected = $option === $this->getActiveFilter();
            $label    = $GLOBALS['TL_LANG']['MSC']['seo_serp_tests.filterRef'][$option];
            $return .= '<option value="'.$option.'"'.($selected ? ' selected="selected"' : '').'>'.$label.'</option>';
        }

        return $return.'</select></div>';
    }

    /**
     * Get the active filter
     *
     * @return string
     */
    protected function getActiveFilter()
    {
        $session = Session::getInstance()->getData();
        $filter  = $session['filter']['tl_page'][$this->filterName];

        return in_array($filter, $this->getAvailableFilters(), true) ? $filter : '';
    }

    /**
     * Get the available filters
     *
     * @return array
     */
    protected function getAvailableFilters()
    {
        return ['all', 'errors', 'warnings'];
    }

    /**
     * Filter the records by message type
     */
    protected function filterRecords()
    {
        $filter = $this->getActiveFilter();

        if (!$filter) {
            return;
        }

        $pageIds = Database::getInstance()->getChildRecords(Session::getInstance()->get('tl_page_node'), 'tl_page');

        if (count($pageIds) === 0) {
            $pageIds = [0];
        }

        $root  = [];
        $pages = Database::getInstance()->execute("SELECT * FROM tl_page WHERE id IN (".implode(',', $pageIds).")");

        while ($pages->next()) {
            $data = $this->generateTests($pages->row());

            if ($filter === 'all') {
                // Add the page to the root if there is at least one message of any type
                foreach ($data as $messages) {
                    if (count($messages) > 0) {
                        $root[] = $pages->id;
                    }
                }
            } elseif (count($data[$filter]) > 0) {
                $root[] = $pages->id;
            }
        }

        $GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root'] = (count($root) === 0) ? [0] : $root;
    }

    /**
     * Limit the global operations
     */
    protected function addGlobalOperations()
    {
        $enabled = $this->isEnabled();

        array_insert($GLOBALS['TL_DCA']['tl_page']['list']['global_operations'], 0, [
            'serp_tests' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC'][$enabled ? 'seo_serp_tests.disable' : 'seo_serp_tests.enable'],
                'href'       => 'serp_tests='.($enabled ? '0' : '1'),
                'icon'       => 'system/modules/seo_serp_preview/assets/icons/tests.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
        ]);
    }

    /**
     * Replace the default label generator
     */
    protected function replaceLabelGenerator()
    {
        // Preserve the default callback
        $GLOBALS['TL_DCA']['tl_page']['list']['label']['default_label_callback'] = $GLOBALS['TL_DCA']['tl_page']['list']['label']['label_callback'];

        $GLOBALS['TL_DCA']['tl_page']['list']['label']['label_callback'] = [
            'Derhaeuptling\SeoSerpPreview\TestsHandler',
            'generateLabel',
        ];
    }

    /**
     * Generate the label
     *
     * @param array         $row
     * @param string        $label
     * @param DataContainer $dc
     * @param string        $imageAttribute
     * @param boolean       $blnReturnImage
     * @param boolean       $blnProtected
     *
     * @return string
     */
    public function generateLabel(
        array $row,
        $label,
        DataContainer $dc = null,
        $imageAttribute = '',
        $blnReturnImage = false,
        $blnProtected = false
    ) {
        $default  = '';
        $callback = $GLOBALS['TL_DCA']['tl_page']['list']['label']['default_label_callback'];

        // Get the default label
        if (is_array($callback)) {
            $default = System::importStatic($callback[0])->{$callback[1]}(
                $row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected
            );
        } elseif (is_callable($callback)) {
            $default = $callback($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);
        }

        $template = new BackendTemplate('be_seo_serp_tests');
        $template->setData($this->generateTests($row));
        $template->label = $default;

        // Add assets
        $GLOBALS['TL_CSS'][]        = 'system/modules/seo_serp_preview/assets/css/tests.min.css';
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/seo_serp_preview/assets/js/tests.min.js';

        return $template->parse();
    }

    /**
     * Generate the tests for a record
     *
     * @param array $data
     *
     * @return array
     */
    protected function generateTests(array $data)
    {
        System::loadLanguageFile('seo_serp_tests');
        $result = ['errors' => [], 'warnings' => []];

        /** @var TestInterface $test */
        foreach (TestsManager::getAll() as $test) {
            try {
                $test->run($data);
            } catch (ErrorException $e) {
                $result['errors'][] = $e->getMessage();
            } catch (WarningException $e) {
                $result['warnings'][] = $e->getMessage();
            }
        }

        return $result;
    }
}
