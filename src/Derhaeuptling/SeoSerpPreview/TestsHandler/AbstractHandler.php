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

use Contao\BackendTemplate;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\Session;
use Contao\System;
use Derhaeuptling\SeoSerpPreview\Test\Exception\ErrorException;
use Derhaeuptling\SeoSerpPreview\Test\Exception\WarningException;
use Derhaeuptling\SeoSerpPreview\Test\TestInterface;
use Derhaeuptling\SeoSerpPreview\TestsManager;

abstract class AbstractHandler
{
    /**
     * Table name
     * @var string
     */
    protected $table;

    /**
     * Data container
     * @var DataContainer
     */
    protected $dc;

    /**
     * Filter name
     * @var string
     */
    public static $filterName = 'seo_serp_tests';

    /**
     * Parameter name
     * @var string
     */
    public static $serpParamName = 'serp_tests';

    /**
     * Initialize the tests handler
     *
     * @param DataContainer|null $dc
     */
    public function initialize(DataContainer $dc = null)
    {
        if ($dc === null) {
            return;
        }

        $this->table = $this->getTableName();
        $this->dc    = $dc;

        $this->addGlobalOperations();

        if ($this->isEnabled()) {
            $this->initializeEnabled();
        }
    }

    /**
     * Initialize the tests handler if enabled
     */
    protected function initializeEnabled()
    {
        $this->addMessageFilter();
        $this->filterRecords();
        $this->replaceLabelGenerator();
    }

    /**
     * Get the table name
     *
     * @return string
     */
    abstract protected function getTableName();

    /**
     * Get the applicable record IDs
     *
     * @return array
     */
    abstract protected function getRecordIds();

    /**
     * Get the global operation
     *
     * @param bool $enabled
     *
     * @return array
     */
    protected function getGlobalOperation($enabled)
    {
        return [
            'label'      => &$GLOBALS['TL_LANG']['MSC'][$enabled ? 'seo_serp_tests.disable' : 'seo_serp_tests.enable'],
            'href'       => static::$serpParamName.'='.($enabled ? '0' : '1'),
            'icon'       => 'system/modules/seo_serp_preview/assets/icons/tests.svg',
            'attributes' => 'onclick="Backend.getScrollOffset()"',
        ];
    }

    /**
     * Return true if the tests are enabled
     *
     * @return bool
     */
    protected function isEnabled()
    {
        return \Input::get(static::$serpParamName) ? true : false;
    }

    /**
     * Add the message filter
     */
    protected function addMessageFilter()
    {
        $GLOBALS['TL_DCA'][$this->table]['list']['sorting']['panelLayout']                           = 'serp_message_filter,'.$GLOBALS['TL_DCA'][$this->table]['list']['sorting']['panelLayout'];
        $GLOBALS['TL_DCA'][$this->table]['list']['sorting']['panel_callback']['serp_message_filter'] = [
            static::class,
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
        $session = Session::getInstance()->getData();

        // Set filter from user input
        if (Input::post('FORM_SUBMIT') === 'tl_filters') {
            if (Input::post(static::$filterName, true) !== 'tl_'.static::$filterName) {
                $session['filter'][$this->table][static::$filterName] = \Input::post(static::$filterName, true);
            } else {
                unset($session['filter'][$this->table][static::$filterName]);
            }

            Session::getInstance()->setData($session);
        }

        $return = '<div class="tl_filter tl_subpanel">
<strong>'.$GLOBALS['TL_LANG']['MSC']['seo_serp_tests.filter'][0].'</strong>
<select name="seo_serp_tests" class="tl_select'.(isset($session['filter'][$this->table][static::$filterName]) ? ' active' : '').'">
<option value="tl_seo_serp_tests">'.$GLOBALS['TL_LANG']['MSC']['seo_serp_tests.filter'][1].'</option>
<option value="tl_seo_serp_tests">---</option>';

        foreach (static::getAvailableFilters() as $option) {
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
        $filter  = $session['filter'][$this->table][static::$filterName];

        return in_array($filter, static::getAvailableFilters(), true) ? $filter : '';
    }

    /**
     * Get the available filters
     *
     * @return array
     */
    public static function getAvailableFilters()
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

        $recordIds = $this->getRecordIds();

        if (count($recordIds) === 0) {
            $recordIds = [0];
        }

        $root    = [];
        $records = Database::getInstance()->execute(
            "SELECT * FROM ".$this->table." WHERE id IN (".implode(',', $recordIds).")"
        );

        while ($records->next()) {
            $data = $this->generateTests($records->row());

            if ($filter === 'all') {
                // Add the record to the root if there is at least one message of any type
                foreach ($data as $messages) {
                    if (count($messages) > 0) {
                        $root[] = $records->id;
                    }
                }
            } elseif (count($data[$filter]) > 0) {
                $root[] = $records->id;
            }
        }

        $GLOBALS['TL_DCA'][$this->table]['list']['sorting']['root'] = (count($root) === 0) ? [0] : $root;
    }

    /**
     * Limit the global operations
     */
    protected function addGlobalOperations()
    {
        array_insert(
            $GLOBALS['TL_DCA'][$this->table]['list']['global_operations'],
            0,
            [
                'serp_tests' => $this->getGlobalOperation($this->isEnabled()),
            ]
        );
    }

    /**
     * Replace the default label generator
     */
    protected function replaceLabelGenerator()
    {
        if ($GLOBALS['TL_DCA'][$this->table]['list']['sorting']['mode'] === 4) {
            $GLOBALS['TL_DCA'][$this->table]['list']['sorting']['default_child_record_callback'] = $GLOBALS['TL_DCA'][$this->table]['list']['sorting']['child_record_callback'];
            $GLOBALS['TL_DCA'][$this->table]['list']['sorting']['child_record_callback']         = [
                static::class,
                'generateLabel',
            ];
        } else {
            $GLOBALS['TL_DCA'][$this->table]['list']['label']['default_label_callback'] = $GLOBALS['TL_DCA'][$this->table]['list']['label']['label_callback'];
            $GLOBALS['TL_DCA'][$this->table]['list']['label']['label_callback']         = [
                static::class,
                'generateLabel',
            ];
        }
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
        $label = null,
        DataContainer $dc = null,
        $imageAttribute = '',
        $blnReturnImage = false,
        $blnProtected = false
    ) {
        $default = '';

        if ($GLOBALS['TL_DCA'][$this->table]['list']['sorting']['mode'] === 4) {
            $callback = $GLOBALS['TL_DCA'][$this->table]['list']['sorting']['default_child_record_callback'];
        } else {
            $callback = $GLOBALS['TL_DCA'][$this->table]['list']['label']['default_label_callback'];
        }

        // Get the default label
        if (is_array($callback)) {
            $default = System::importStatic($callback[0])->{$callback[1]}(
                $row,
                $label,
                $dc,
                $imageAttribute,
                $blnReturnImage,
                $blnProtected
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
            // Skip the unsupported tests
            if (!$test->supports($this->table)) {
                continue;
            }

            try {
                $test->run($data, $this->table);
            } catch (ErrorException $e) {
                $result['errors'][] = $e->getMessage();
            } catch (WarningException $e) {
                $result['warnings'][] = $e->getMessage();
            }
        }

        return $result;
    }
}