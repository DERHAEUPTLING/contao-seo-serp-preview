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

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Input;
use Contao\Session;

class PageHandler extends AbstractHandler
{
    /**
     * Initialize the tests handler
     *
     * @param DataContainer|null $dc
     */
    public function initialize(DataContainer $dc = null)
    {
        $pages = Database::getInstance()->prepare("SELECT id FROM tl_page WHERE type='root' AND seo_serp_ignore=''")
            ->limit(1)
            ->execute();

        // Do not initialize handler if there are no pages to analyze
        if (!$pages->numRows) {
            unset($GLOBALS['TL_DCA']['tl_page']['fields']['seo_serp_preview']);

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
        return 'tl_page';
    }

    /**
     * Initialize the tests handler if enabled
     */
    protected function initializeEnabled()
    {
        parent::initializeEnabled();

        $this->autoExpandTree();
    }

    /**
     * Auto expand the tree
     *
     * @throws \Exception
     */
    public function autoExpandTree()
    {
        $session = Session::getInstance()->getData();

        if ($session['seo_serp_expand_tree'] !== 'tl_page' && !Input::get('serp_tests_expand')) {
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
        Backend::redirect(str_replace('serp_tests_expand=1', '', Environment::get('request')));
    }

    /**
     * Generate the message filter
     *
     * @return string
     */
    public function generateMessageFilter()
    {
        if (Input::post('FORM_SUBMIT') === 'tl_filters'
            && Input::post($this->filterName, true) !== 'tl_'.$this->filterName
        ) {
            $session                         = Session::getInstance()->getData();
            $session['seo_serp_expand_tree'] = 'tl_page';
            Session::getInstance()->setData($session);
        }

        return parent::generateMessageFilter();
    }

    /**
     * Get the applicable record IDs
     *
     * @return array
     */
    protected function getRecordIds()
    {
        $pageNode = Session::getInstance()->get('tl_page_node');

        return array_merge([$pageNode], Database::getInstance()->getChildRecords($pageNode, 'tl_page'));
    }

    /**
     * Filter the records by message type
     */
    protected function filterRecords()
    {
        parent::filterRecords();
        $pageNode = Session::getInstance()->get('tl_page_node');

        // If there is a page node selected and there are no root IDs (e.g. due to filter settings),
        // make sure that page node is displayed
        if ($pageNode && $GLOBALS['TL_DCA'][$this->table]['list']['sorting']['root'] === [0]) {
            $GLOBALS['TL_DCA'][$this->table]['list']['sorting']['root'] = [$pageNode];
        }
    }

    /**
     * Get the global operation
     *
     * @param bool $enabled
     *
     * @return array
     */
    protected function getGlobalOperation($enabled)
    {
        $operation = parent::getGlobalOperation($enabled);

        if ($enabled) {
            $operation['href'] .= '&serp_tests_expand=1';
        }

        return $operation;
    }
}
